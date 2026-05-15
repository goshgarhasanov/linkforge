<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

final class AdminStatsService
{
    public function overview(): array
    {
        $now = new \DateTimeImmutable();
        $monthAgo = $now->modify('-30 days');
        $dayAgo = $now->modify('-24 hours');

        return [
            'users' => [
                'total'           => User::query()->count(),
                'active'          => User::query()->where('is_active', true)->count(),
                'new_last_30d'    => User::query()->where('created_at', '>=', $monthAgo)->count(),
                'verified'        => User::query()->whereNotNull('email_verified_at')->count(),
            ],
            'links' => [
                'total'           => Link::query()->count(),
                'active'          => Link::query()->where('is_active', true)->count(),
                'flagged'         => Link::query()->where('is_flagged', true)->count(),
                'expired'         => Link::query()->whereNotNull('expires_at')->where('expires_at', '<', $now)->count(),
                'new_last_30d'    => Link::query()->where('created_at', '>=', $monthAgo)->count(),
            ],
            'clicks' => [
                'total'           => (int) Link::query()->sum('click_count'),
                'unique_total'    => (int) Link::query()->sum('unique_click_count'),
                'last_24h'        => Click::query()->where('clicked_at', '>=', $dayAgo)->where('is_bot', false)->count(),
                'last_30d'        => Click::query()->where('clicked_at', '>=', $monthAgo)->where('is_bot', false)->count(),
                'bots_blocked_30d' => Click::query()->where('clicked_at', '>=', $monthAgo)->where('is_bot', true)->count(),
            ],
        ];
    }

    public function growth(int $days = 30): array
    {
        $start = (new \DateTimeImmutable())->modify("-{$days} days")->setTime(0, 0);
        $startFmt = $start->format('Y-m-d H:i:s');

        $users = DB::table('users')
            ->selectRaw('DATE(created_at) as bucket, COUNT(*) as total')
            ->where('created_at', '>=', $startFmt)
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $links = DB::table('links')
            ->selectRaw('DATE(created_at) as bucket, COUNT(*) as total')
            ->where('created_at', '>=', $startFmt)
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $clicks = DB::table('clicks')
            ->selectRaw('DATE(clicked_at) as bucket, COUNT(*) as total')
            ->where('clicked_at', '>=', $startFmt)
            ->where('is_bot', false)
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $series = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable())->modify("-{$i} days")->format('Y-m-d');
            $series[] = [
                'date'   => $date,
                'users'  => (int) ($users[$date]->total  ?? 0),
                'links'  => (int) ($links[$date]->total  ?? 0),
                'clicks' => (int) ($clicks[$date]->total ?? 0),
            ];
        }

        return $series;
    }

    public function topUsers(int $limit = 10): array
    {
        return User::query()
            ->select([
                'users.id', 'users.uuid', 'users.name', 'users.email', 'users.role',
                DB::raw('COUNT(links.id) as link_count'),
                DB::raw('COALESCE(SUM(links.click_count), 0) as total_clicks'),
            ])
            ->leftJoin('links', 'links.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.uuid', 'users.name', 'users.email', 'users.role')
            ->orderByDesc('total_clicks')
            ->limit($limit)
            ->get()
            ->map(static fn ($u) => [
                'uuid'         => $u->uuid,
                'name'         => $u->name,
                'email'        => $u->email,
                'role'         => $u->role,
                'link_count'   => (int) $u->link_count,
                'total_clicks' => (int) $u->total_clicks,
            ])
            ->all();
    }

    public function systemHealth(): array
    {
        return [
            'database'  => $this->checkDatabase(),
            'redis'     => $this->checkRedis(),
            'disk'      => $this->checkDisk(),
            'php'       => [
                'status'   => 'ok',
                'version'  => PHP_VERSION,
                'memory'   => $this->formatBytes(memory_get_usage(true)),
                'opcache'  => function_exists('opcache_get_status') ? 'enabled' : 'disabled',
            ],
        ];
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo()->query('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return ['status' => 'ok', 'latency_ms' => $latency];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            $config = require dirname(__DIR__, 2) . '/config/database.php';
            $redis = new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => $config['redis']['host'],
                'port'   => $config['redis']['port'],
            ]);

            $start = microtime(true);
            $redis->ping();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return ['status' => 'ok', 'latency_ms' => $latency];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    private function checkDisk(): array
    {
        $path = dirname(__DIR__, 2) . '/storage';
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if ($free === false || $total === false || $total === 0.0) {
            return ['status' => 'unknown'];
        }

        $usedPercent = round((1 - $free / $total) * 100, 1);

        return [
            'status'       => $usedPercent < 90 ? 'ok' : 'warning',
            'free'         => $this->formatBytes((int) $free),
            'total'        => $this->formatBytes((int) $total),
            'used_percent' => $usedPercent,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $b = (float) $bytes;
        while ($b >= 1024 && $i < 4) {
            $b /= 1024;
            $i++;
        }

        return round($b, 2) . ' ' . $units[$i];
    }
}
