<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\AnalyticsService;
use App\Services\LinkService;
use App\Support\Exceptions\HttpException;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AnalyticsController
{
    public function __construct(
        private readonly LinkService $links,
        private readonly AnalyticsService $analytics,
    ) {
    }

    public function show(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $params = $request->getQueryParams();
        $days = max(1, min(365, (int) ($params['days'] ?? 30)));

        try {
            $link = $this->links->findByCode((string) $args['code']);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        if ($link->user_id !== $user->id) {
            return JsonResponder::error('Bu linkin analitikasına icazəniz yoxdur.', 403);
        }

        return JsonResponder::success([
            'summary'    => $this->analytics->summary($link),
            'timeseries' => $this->analytics->timeseries($link, $days),
            'devices'    => $this->analytics->breakdown($link, 'device_type'),
            'browsers'   => $this->analytics->breakdown($link, 'browser'),
            'os'         => $this->analytics->breakdown($link, 'os'),
            'countries'  => $this->analytics->breakdown($link, 'country_name'),
            'referrers'  => $this->analytics->breakdown($link, 'referrer_host'),
            'languages'  => $this->analytics->breakdown($link, 'language', 5),
            'heatmap'    => $this->analytics->hourlyHeatmap($link),
        ]);
    }
}
