<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;

final class WebhookDispatcher
{
    private const TIMEOUT_SECONDS = 5;
    private const MAX_FAILURES_BEFORE_DISABLE = 10;

    public function dispatch(User $user, string $event, array $payload): void
    {
        $hooks = Webhook::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($hooks as $hook) {
            if (! $hook->listensTo($event)) {
                continue;
            }

            $this->deliver($hook, $event, $payload);
        }
    }

    private function deliver(Webhook $hook, string $event, array $payload): void
    {
        $body = json_encode([
            'event'      => $event,
            'timestamp'  => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'data'       => $payload,
        ], JSON_UNESCAPED_UNICODE);

        $signature = hash_hmac('sha256', $body, $hook->secret);
        $start = microtime(true);

        $ch = curl_init($hook->url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'User-Agent: LinkForge-Webhooks/1.0',
                'X-LinkForge-Event: ' . $event,
                'X-LinkForge-Signature: sha256=' . $signature,
                'X-LinkForge-Delivery-Id: ' . bin2hex(random_bytes(8)),
            ],
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $duration = (int) round((microtime(true) - $start) * 1000);
        $success = $status >= 200 && $status < 300;

        WebhookDelivery::query()->create([
            'webhook_id'     => $hook->id,
            'event'          => $event,
            'payload'        => $payload,
            'status_code'    => $status ?: null,
            'response_body'  => $response !== false ? mb_substr((string) $response, 0, 1000) : $error,
            'duration_ms'    => $duration,
            'was_successful' => $success,
        ]);

        if ($success) {
            $hook->forceFill([
                'failure_count'     => 0,
                'last_delivered_at' => now(),
                'last_response'     => 'HTTP ' . $status,
            ])->save();
        } else {
            $newFailures = $hook->failure_count + 1;
            $update = ['failure_count' => $newFailures, 'last_response' => $error ?: 'HTTP ' . $status];

            if ($newFailures >= self::MAX_FAILURES_BEFORE_DISABLE) {
                $update['is_active'] = false;
            }

            $hook->forceFill($update)->save();
        }
    }
}
