<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Psr\Http\Message\ServerRequestInterface;

final class AuditLogger
{
    public function record(
        AuditAction $action,
        ?User $actor = null,
        ?string $targetType = null,
        ?int $targetId = null,
        array $metadata = [],
        ?ServerRequestInterface $request = null,
    ): AuditLog {
        $log = new AuditLog([
            'actor_id'    => $actor?->id,
            'action'      => $action->value,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'metadata'    => $metadata !== [] ? $metadata : null,
            'ip_address'  => $request !== null ? $this->extractIp($request) : null,
            'user_agent'  => $request?->getHeaderLine('User-Agent') ?: null,
            'created_at'  => now(),
        ]);
        $log->save();

        return $log;
    }

    private function extractIp(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();

        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (! empty($server[$key])) {
                return trim(explode(',', $server[$key])[0]);
            }
        }

        return '0.0.0.0';
    }
}
