<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Predis\Client as RedisClient;

final class NotificationService
{
    public function __construct(
        private readonly RedisClient $redis,
    ) {
    }

    public function notify(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        array $metadata = [],
    ): Notification {
        $notification = Notification::query()->create([
            'user_id'    => $user->id,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'action_url' => $actionUrl,
            'metadata'   => $metadata !== [] ? $metadata : null,
        ]);

        $this->publishToStream($user->id, [
            'type'    => 'notification',
            'payload' => [
                'uuid'       => $notification->uuid,
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'action_url' => $actionUrl,
                'created_at' => $notification->created_at->toIso8601String(),
            ],
        ]);

        return $notification;
    }

    public function publishToStream(int $userId, array $message): void
    {
        try {
            $this->redis->publish('lf:user:' . $userId, json_encode($message));
        } catch (\Throwable) {
        }
    }

    public function unreadCount(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function listFor(User $user, int $limit = 20): array
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(static fn (Notification $n) => [
                'uuid'       => $n->uuid,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'action_url' => $n->action_url,
                'is_read'    => $n->read_at !== null,
                'created_at' => $n->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function markAllRead(User $user): int
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
