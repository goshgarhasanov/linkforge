<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class NotificationController
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        return JsonResponder::success([
            'unread'        => $this->notifications->unreadCount($user),
            'notifications' => $this->notifications->listFor($user, 50),
        ]);
    }

    public function markRead(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $n = Notification::query()->where('uuid', $args['uuid'])->where('user_id', $user->id)->first();
        if (! $n instanceof Notification) {
            return JsonResponder::error('Bildiriş tapılmadı.', 404);
        }

        $n->markAsRead();

        return JsonResponder::noContent();
    }

    public function markAllRead(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $count = $this->notifications->markAllRead($user);

        return JsonResponder::success(['marked' => $count]);
    }
}
