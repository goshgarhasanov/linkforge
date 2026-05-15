<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AuditLog;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuditLogController
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(100, max(10, (int) ($params['per_page'] ?? 30)));

        $query = AuditLog::query()
            ->with(['actor:id,uuid,name,email'])
            ->orderByDesc('created_at');

        if (! empty($params['action'])) {
            $query->where('action', $params['action']);
        }

        if (! empty($params['actor_uuid'])) {
            $query->whereHas('actor', static fn ($q) => $q->where('uuid', $params['actor_uuid']));
        }

        $total = (clone $query)->count();
        $logs = $query->forPage($page, $perPage)->get();

        return JsonResponder::success(
            $logs->map(static fn (AuditLog $log) => [
                'id'           => $log->id,
                'action'       => $log->action->value,
                'action_label' => $log->action->label(),
                'actor'        => $log->actor ? [
                    'uuid'  => $log->actor->uuid,
                    'name'  => $log->actor->name,
                    'email' => $log->actor->email,
                ] : null,
                'target_type'  => $log->target_type,
                'target_id'    => $log->target_id,
                'metadata'     => $log->metadata,
                'ip_address'   => $log->ip_address,
                'created_at'   => $log->created_at?->toIso8601String(),
            ])->all(),
            meta: [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        );
    }
}
