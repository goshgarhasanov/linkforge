<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Database\Capsule\Manager as DB;

final class AnalyticsService
{
    public function summary(Link $link): array
    {
        $now = new \DateTimeImmutable();
        $last24h = $now->modify('-24 hours');
        $last7d  = $now->modify('-7 days');
        $last30d = $now->modify('-30 days');

        return [
            'total_clicks'       => (int) $link->click_count,
            'unique_clicks'      => (int) $link->unique_click_count,
            'clicks_last_24h'    => $this->countSince($link, $last24h),
            'clicks_last_7d'     => $this->countSince($link, $last7d),
            'clicks_last_30d'    => $this->countSince($link, $last30d),
            'avg_per_day_30d'    => round($this->countSince($link, $last30d) / 30, 2),
            'top_country'        => $this->topByColumn($link, 'country_name'),
            'top_referrer'       => $this->topByColumn($link, 'referrer_host'),
        ];
    }

    public function timeseries(Link $link, int $days = 30): array
    {
        $start = (new \DateTimeImmutable())->modify("-{$days} days")->setTime(0, 0);

        $rows = DB::table('clicks')
            ->selectRaw('DATE(clicked_at) as bucket, COUNT(*) as total, SUM(is_unique) as uniques')
            ->where('link_id', $link->id)
            ->where('is_bot', false)
            ->where('clicked_at', '>=', $start->format('Y-m-d H:i:s'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $series = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable())->modify("-{$i} days")->format('Y-m-d');
            $row = $rows[$date] ?? null;

            $series[] = [
                'date'    => $date,
                'clicks'  => (int) ($row->total   ?? 0),
                'uniques' => (int) ($row->uniques ?? 0),
            ];
        }

        return $series;
    }

    public function breakdown(Link $link, string $dimension, int $limit = 10): array
    {
        $allowed = ['country_name', 'country_code', 'device_type', 'browser', 'os', 'referrer_host', 'language'];

        if (! in_array($dimension, $allowed, true)) {
            throw new \InvalidArgumentException("Etibarsız ölçü: {$dimension}");
        }

        $rows = DB::table('clicks')
            ->select([
                $dimension . ' as label',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(is_unique) as uniques'),
            ])
            ->where('link_id', $link->id)
            ->where('is_bot', false)
            ->whereNotNull($dimension)
            ->groupBy($dimension)
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return $rows->map(static fn ($row) => [
            'label'   => (string) $row->label,
            'clicks'  => (int) $row->total,
            'uniques' => (int) $row->uniques,
        ])->all();
    }

    public function hourlyHeatmap(Link $link): array
    {
        $rows = DB::table('clicks')
            ->selectRaw('DAYOFWEEK(clicked_at) as dow, HOUR(clicked_at) as hour, COUNT(*) as total')
            ->where('link_id', $link->id)
            ->where('is_bot', false)
            ->where('clicked_at', '>=', (new \DateTimeImmutable('-90 days'))->format('Y-m-d H:i:s'))
            ->groupBy('dow', 'hour')
            ->get();

        $heatmap = array_fill(0, 7, array_fill(0, 24, 0));

        foreach ($rows as $row) {
            $heatmap[((int) $row->dow + 5) % 7][(int) $row->hour] = (int) $row->total;
        }

        return $heatmap;
    }

    private function countSince(Link $link, \DateTimeImmutable $since): int
    {
        return Click::query()
            ->where('link_id', $link->id)
            ->where('is_bot', false)
            ->where('clicked_at', '>=', $since)
            ->count();
    }

    private function topByColumn(Link $link, string $column): ?string
    {
        $row = DB::table('clicks')
            ->select($column)
            ->where('link_id', $link->id)
            ->where('is_bot', false)
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->first();

        return $row?->{$column};
    }
}
