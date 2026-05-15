<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Exceptions\HttpException;
use App\Support\Http\JsonResponder;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserAdminController
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

        $query = User::query()
            ->select([
                'users.*',
                DB::raw('(SELECT COUNT(*) FROM links WHERE links.user_id = users.id) as link_count'),
                DB::raw('(SELECT COALESCE(SUM(click_count), 0) FROM links WHERE links.user_id = users.id) as total_clicks'),
            ])
            ->orderByDesc('users.created_at');

        if (! empty($params['search'])) {
            $term = '%' . trim((string) $params['search']) . '%';
            $query->where(static function ($q) use ($term): void {
                $q->where('users.name', 'like', $term)->orWhere('users.email', 'like', $term);
            });
        }

        if (! empty($params['role']) && in_array($params['role'], array_column(UserRole::cases(), 'value'), true)) {
            $query->where('users.role', $params['role']);
        }

        if (isset($params['status'])) {
            if ($params['status'] === 'active')   $query->where('users.is_active', true);
            if ($params['status'] === 'inactive') $query->where('users.is_active', false);
        }

        $total = (clone $query)->count();
        $users = $query->forPage($page, $perPage)->get();

        return JsonResponder::success(
            $users->map(static fn ($u) => [
                'uuid'              => $u->uuid,
                'name'              => $u->name,
                'email'             => $u->email,
                'role'              => $u->role->value,
                'role_label'        => $u->role->label(),
                'is_active'         => (bool) $u->is_active,
                'email_verified'    => $u->email_verified_at !== null,
                'two_factor'        => (bool) $u->two_factor_enabled,
                'link_count'        => (int) $u->link_count,
                'total_clicks'      => (int) $u->total_clicks,
                'last_login_at'     => $u->last_login_at?->toIso8601String(),
                'last_login_ip'     => $u->last_login_ip,
                'created_at'        => $u->created_at?->toIso8601String(),
            ])->all(),
            meta: [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        );
    }

    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = User::query()->where('uuid', $args['uuid'])->first();

        if (! $user instanceof User) {
            return JsonResponder::error('İstifadəçi tapılmadı.', 404);
        }

        $linkCount   = $user->links()->count();
        $totalClicks = (int) $user->links()->sum('click_count');

        return JsonResponder::success([
            'uuid'           => $user->uuid,
            'name'           => $user->name,
            'email'          => $user->email,
            'role'           => $user->role->value,
            'role_label'     => $user->role->label(),
            'is_active'      => (bool) $user->is_active,
            'email_verified' => $user->email_verified_at !== null,
            'two_factor'     => (bool) $user->two_factor_enabled,
            'locale'         => $user->locale,
            'avatar_url'     => $user->avatar_url,
            'link_count'     => $linkCount,
            'total_clicks'   => $totalClicks,
            'last_login_at'  => $user->last_login_at?->toIso8601String(),
            'last_login_ip'  => $user->last_login_ip,
            'created_at'     => $user->created_at?->toIso8601String(),
        ]);
    }

    public function updateRole(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        $target = User::query()->where('uuid', $args['uuid'])->first();
        if (! $target instanceof User) {
            return JsonResponder::error('İstifadəçi tapılmadı.', 404);
        }

        if ($target->id === $actor->id) {
            return JsonResponder::error('Öz rolunuzu dəyişdirə bilməzsiniz.', 422);
        }

        if ($actor->role !== UserRole::SuperAdmin && $target->role === UserRole::SuperAdmin) {
            return JsonResponder::error('Super administrator rolunu yalnız başqa super admin dəyişdirə bilər.', 403);
        }

        try {
            $newRole = UserRole::from((string) ($body['role'] ?? ''));
        } catch (\ValueError) {
            return JsonResponder::error('Etibarsız rol.', 422);
        }

        if ($newRole === UserRole::SuperAdmin && $actor->role !== UserRole::SuperAdmin) {
            return JsonResponder::error('Yalnız super admin başqa istifadəçini super admin edə bilər.', 403);
        }

        $oldRole = $target->role->value;
        $target->forceFill(['role' => $newRole])->save();

        $this->audit->record(
            AuditAction::UserRoleChanged,
            $actor,
            'user',
            $target->id,
            ['from' => $oldRole, 'to' => $newRole->value],
            $request,
        );

        return JsonResponder::success([
            'uuid' => $target->uuid,
            'role' => $target->role->value,
        ]);
    }

    public function toggleActive(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->getAttribute('user');

        $target = User::query()->where('uuid', $args['uuid'])->first();
        if (! $target instanceof User) {
            return JsonResponder::error('İstifadəçi tapılmadı.', 404);
        }

        if ($target->id === $actor->id) {
            return JsonResponder::error('Öz hesabınızı deaktiv edə bilməzsiniz.', 422);
        }

        $newState = ! $target->is_active;
        $target->forceFill(['is_active' => $newState])->save();

        $this->audit->record(
            $newState ? AuditAction::UserUnbanned : AuditAction::UserBanned,
            $actor,
            'user',
            $target->id,
            ['target_email' => $target->email],
            $request,
        );

        return JsonResponder::success([
            'uuid'      => $target->uuid,
            'is_active' => $newState,
        ]);
    }
}
