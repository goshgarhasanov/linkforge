<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Enums\SubscriptionPlan;
use App\Models\User;
use App\Services\BillingService;
use App\Support\Exceptions\HttpException;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BillingController
{
    public function __construct(
        private readonly BillingService $billing,
    ) {
    }

    public function current(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $plan = $this->billing->userPlan($user);

        $subscription = $user->subscriptions()->whereIn('status', ['trialing', 'active'])->latest()->first();

        return JsonResponder::success([
            'plan'                 => $plan->value,
            'plan_label'           => $plan->label(),
            'monthly_price'        => $plan->monthlyPrice(),
            'link_limit'           => $plan->linkLimit(),
            'features'             => $plan->features(),
            'status'               => $subscription?->status,
            'current_period_end'   => $subscription?->current_period_end?->toIso8601String(),
        ]);
    }

    public function plans(): ResponseInterface
    {
        return JsonResponder::success(
            array_map(static fn (SubscriptionPlan $p) => [
                'key'           => $p->value,
                'label'         => $p->label(),
                'monthly_price' => $p->monthlyPrice(),
                'link_limit'    => $p->linkLimit(),
                'features'      => $p->features(),
            ], SubscriptionPlan::cases()),
        );
    }

    public function checkout(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        try {
            $plan = SubscriptionPlan::from((string) ($body['plan'] ?? ''));
            $url = $this->billing->createCheckoutSession($user, $plan);
        } catch (\ValueError) {
            return JsonResponder::error('Etibarsız plan.', 422);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::success(['checkout_url' => $url]);
    }

    public function webhook(ServerRequestInterface $request): ResponseInterface
    {
        $payload = (string) $request->getBody();
        $signature = $request->getHeaderLine('Stripe-Signature');

        try {
            $this->billing->handleWebhook($payload, $signature);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::success(['received' => true]);
    }
}
