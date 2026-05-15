<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Enums\AuditAction;
use App\Models\Link;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LinkAdminController
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(100, max(10, (int) ($params['per_page'] ?? 25)));

        $query = Link::query()
            ->with(['user:id,uuid,name,email'])
            ->latest();

        if (! empty($params['search'])) {
            $term = '%' . trim((string) $params['search']) . '%';
            $query->where(static function ($q) use ($term): void {
                $q->where('short_code', 'like', $term)
                    ->orWhere('original_url', 'like', $term)
                    ->orWhere('title', 'like', $term);
            });
        }

        if (isset($params['status'])) {
            if ($params['status'] === 'flagged')  $query->where('is_flagged', true);
            if ($params['status'] === 'inactive') $query->where('is_active', false);
            if ($params['status'] === 'active')   $query->where('is_active', true)->where('is_flagged', false);
        }

        $total = (clone $query)->count();
        $links = $query->forPage($page, $perPage)->get();

        return JsonResponder::success(
            $links->map(static fn (Link $link) => [
                'uuid'         => $link->uuid,
                'short_code'   => $link->short_code,
                'original_url' => $link->original_url,
                'title'        => $link->title,
                'click_count'  => $link->click_count,
                'is_active'    => $link->is_active,
                'is_flagged'   => $link->is_flagged,
                'flag_reason'  => $link->flag_reason,
                'user'         => $link->user instanceof User ? [
                    'uuid'  => $link->user->uuid,
                    'name'  => $link->user->name,
                    'email' => $link->user->email,
                ] : null,
                'created_at'   => $link->created_at?->toIso8601String(),
            ])->all(),
            meta: [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        );
    }

    public function flag(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        $link = Link::query()->where('uuid', $args['uuid'])->first();
        if (! $link instanceof Link) {
            return JsonResponder::error('Link tapılmadı.', 404);
        }

        $reason = trim((string) ($body['reason'] ?? 'Manual moderasiya'));

        $link->forceFill([
            'is_flagged'  => true,
            'flag_reason' => mb_substr($reason, 0, 255),
        ])->save();

        $this->audit->record(
            AuditAction::LinkFlagged,
            $actor,
            'link',
            $link->id,
            ['reason' => $reason, 'short_code' => $link->short_code],
            $request,
        );

        return JsonResponder::success(['uuid' => $link->uuid, 'is_flagged' => true]);
    }

    public function unflag(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->getAttribute('user');

        $link = Link::query()->where('uuid', $args['uuid'])->first();
        if (! $link instanceof Link) {
            return JsonResponder::error('Link tapılmadı.', 404);
        }

        $link->forceFill(['is_flagged' => false, 'flag_reason' => null])->save();

        $this->audit->record(
            AuditAction::LinkUnflagged,
            $actor,
            'link',
            $link->id,
            ['short_code' => $link->short_code],
            $request,
        );

        return JsonResponder::success(['uuid' => $link->uuid, 'is_flagged' => false]);
    }

    public function toggleActive(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->getAttribute('user');

        $link = Link::query()->where('uuid', $args['uuid'])->first();
        if (! $link instanceof Link) {
            return JsonResponder::error('Link tapılmadı.', 404);
        }

        $newState = ! $link->is_active;
        $link->forceFill(['is_active' => $newState])->save();

        $this->audit->record(
            $newState ? AuditAction::LinkActivated : AuditAction::LinkDeactivated,
            $actor,
            'link',
            $link->id,
            ['short_code' => $link->short_code],
            $request,
        );

        return JsonResponder::success(['uuid' => $link->uuid, 'is_active' => $newState]);
    }

    public function destroy(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->getAttribute('user');

        $link = Link::query()->where('uuid', $args['uuid'])->first();
        if (! $link instanceof Link) {
            return JsonResponder::error('Link tapılmadı.', 404);
        }

        $shortCode = $link->short_code;
        $linkId = $link->id;
        $link->delete();

        $this->audit->record(
            AuditAction::LinkDeleted,
            $actor,
            'link',
            $linkId,
            ['short_code' => $shortCode],
            $request,
        );

        return JsonResponder::noContent();
    }
}
