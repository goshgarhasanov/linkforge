<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Exceptions\HttpException;
use Illuminate\Database\Capsule\Manager as DB;

final class BillingService
{
    public function userPlan(User $user): SubscriptionPlan
    {
        $subscription = $user->subscriptions()->whereIn('status', ['trialing', 'active'])->latest()->first();

        return $subscription?->plan ?? SubscriptionPlan::Free;
    }

    public function createCheckoutSession(User $user, SubscriptionPlan $plan): string
    {
        if ($plan === SubscriptionPlan::Free) {
            throw new HttpException(422, 'Pulsuz plan üçün ödəniş tələb olunmur.');
        }

        $priceId = $plan === SubscriptionPlan::Pro
            ? ($_ENV['STRIPE_PRICE_PRO'] ?? '')
            : ($_ENV['STRIPE_PRICE_ENTERPRISE'] ?? '');

        $secret = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        if ($secret === '' || $priceId === '') {
            throw new HttpException(500, 'Billing inteqrasiyası konfiqurasiya edilməyib.');
        }

        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/');

        $params = http_build_query([
            'mode'                      => 'subscription',
            'success_url'               => $appUrl . '/dashboard/settings?billing=success',
            'cancel_url'                => $appUrl . '/dashboard/settings?billing=canceled',
            'customer_email'            => $user->email,
            'client_reference_id'       => (string) $user->id,
            'line_items[0][price]'      => $priceId,
            'line_items[0][quantity]'   => 1,
            'subscription_data[metadata][user_id]'   => (string) $user->id,
            'subscription_data[metadata][user_uuid]' => $user->uuid,
        ]);

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $secret,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200 || $response === false) {
            throw new HttpException(502, 'Stripe ilə əlaqə qurula bilmədi.');
        }

        $data = json_decode((string) $response, true);

        return (string) ($data['url'] ?? throw new HttpException(502, 'Stripe checkout sessiyası yaradılmadı.'));
    }

    public function handleWebhook(string $payload, string $signature): void
    {
        $secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        if ($secret !== '' && ! $this->verifySignature($payload, $signature, $secret)) {
            throw new HttpException(401, 'Etibarsız Stripe imzası.');
        }

        $event = json_decode($payload, true);
        if (! is_array($event) || empty($event['id']) || empty($event['type'])) {
            throw new HttpException(422, 'Etibarsız webhook payload-u.');
        }

        $exists = DB::table('billing_events')->where('stripe_event_id', $event['id'])->exists();
        if ($exists) {
            return;
        }

        $obj = $event['data']['object'] ?? [];
        $userId = $this->resolveUserId($obj);

        DB::table('billing_events')->insert([
            'stripe_event_id' => $event['id'],
            'event_type'      => $event['type'],
            'user_id'         => $userId,
            'payload'         => json_encode($event),
            'processed_at'    => now(),
            'created_at'      => now(),
        ]);

        match ($event['type']) {
            'checkout.session.completed'    => $this->handleCheckoutCompleted($obj, $userId),
            'customer.subscription.created',
            'customer.subscription.updated' => $this->upsertSubscription($obj, $userId),
            'customer.subscription.deleted' => $this->handleSubscriptionCanceled($obj),
            default                         => null,
        };
    }

    private function handleCheckoutCompleted(array $obj, ?int $userId): void
    {
        if ($userId === null || empty($obj['subscription'])) {
            return;
        }

        Subscription::query()->updateOrCreate(
            ['stripe_subscription_id' => $obj['subscription']],
            [
                'user_id'            => $userId,
                'stripe_customer_id' => $obj['customer'] ?? null,
                'status'             => 'active',
                'plan'               => SubscriptionPlan::Pro,
            ],
        );
    }

    private function upsertSubscription(array $obj, ?int $userId): void
    {
        if (empty($obj['id'])) return;

        $priceId = $obj['items']['data'][0]['price']['id'] ?? null;
        $plan = match ($priceId) {
            $_ENV['STRIPE_PRICE_ENTERPRISE'] ?? null => SubscriptionPlan::Enterprise,
            default                                  => SubscriptionPlan::Pro,
        };

        Subscription::query()->updateOrCreate(
            ['stripe_subscription_id' => $obj['id']],
            [
                'user_id'              => $userId,
                'plan'                 => $plan,
                'status'               => $obj['status']   ?? 'active',
                'stripe_customer_id'   => $obj['customer'] ?? null,
                'stripe_price_id'      => $priceId,
                'current_period_start' => isset($obj['current_period_start']) ? (new \DateTimeImmutable())->setTimestamp($obj['current_period_start']) : null,
                'current_period_end'   => isset($obj['current_period_end'])   ? (new \DateTimeImmutable())->setTimestamp($obj['current_period_end'])   : null,
                'trial_ends_at'        => isset($obj['trial_end']) ? (new \DateTimeImmutable())->setTimestamp($obj['trial_end']) : null,
                'canceled_at'          => isset($obj['canceled_at']) ? (new \DateTimeImmutable())->setTimestamp($obj['canceled_at']) : null,
            ],
        );
    }

    private function handleSubscriptionCanceled(array $obj): void
    {
        if (empty($obj['id'])) return;

        Subscription::query()
            ->where('stripe_subscription_id', $obj['id'])
            ->update(['status' => 'canceled', 'canceled_at' => now()]);
    }

    private function resolveUserId(array $obj): ?int
    {
        if (! empty($obj['metadata']['user_id'])) {
            return (int) $obj['metadata']['user_id'];
        }

        if (! empty($obj['client_reference_id'])) {
            return (int) $obj['client_reference_id'];
        }

        return null;
    }

    private function verifySignature(string $payload, string $headerSig, string $secret): bool
    {
        if (! preg_match('/t=(\d+),v1=([0-9a-f]+)/', $headerSig, $m)) {
            return false;
        }

        $expected = hash_hmac('sha256', $m[1] . '.' . $payload, $secret);

        return hash_equals($expected, $m[2]);
    }
}
