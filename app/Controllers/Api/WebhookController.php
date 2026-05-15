<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\User;
use App\Models\Webhook;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class WebhookController
{
    private const ALLOWED_EVENTS = ['link.created', 'link.clicked', 'link.expired', 'link.deleted', '*'];

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $hooks = Webhook::query()->where('user_id', $user->id)->latest()->get();

        return JsonResponder::success(
            $hooks->map(static fn (Webhook $h) => [
                'id'                => $h->id,
                'url'               => $h->url,
                'events'            => $h->events,
                'is_active'         => $h->is_active,
                'failure_count'     => $h->failure_count,
                'last_delivered_at' => $h->last_delivered_at?->toIso8601String(),
                'last_response'     => $h->last_response,
                'created_at'        => $h->created_at?->toIso8601String(),
            ])->all(),
        );
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        $url    = trim((string) ($body['url'] ?? ''));
        $events = (array) ($body['events'] ?? ['*']);

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! str_starts_with($url, 'https://')) {
            return JsonResponder::error('Webhook URL https:// ilə başlamalıdır.', 422);
        }

        $invalid = array_diff($events, self::ALLOWED_EVENTS);
        if ($invalid !== []) {
            return JsonResponder::error('Etibarsız event-lər: ' . implode(', ', $invalid), 422);
        }

        $secret = bin2hex(random_bytes(24));

        $hook = Webhook::query()->create([
            'user_id'   => $user->id,
            'url'       => $url,
            'secret'    => $secret,
            'events'    => array_values(array_unique($events)),
            'is_active' => true,
        ]);

        return JsonResponder::created([
            'id'      => $hook->id,
            'url'     => $hook->url,
            'events'  => $hook->events,
            'secret'  => $secret,
            'warning' => 'Bu secret yalnız bir dəfə göstərilir. İmza doğrulaması üçün saxlayın.',
        ]);
    }

    public function destroy(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $hook = Webhook::query()->where('id', (int) $args['id'])->where('user_id', $user->id)->first();
        if (! $hook instanceof Webhook) {
            return JsonResponder::error('Webhook tapılmadı.', 404);
        }

        $hook->delete();

        return JsonResponder::noContent();
    }
}
