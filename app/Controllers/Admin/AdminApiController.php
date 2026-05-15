<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AdminStatsService;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AdminApiController
{
    public function __construct(
        private readonly AdminStatsService $stats,
    ) {
    }

    public function overview(ServerRequestInterface $request): ResponseInterface
    {
        $days = max(7, min(90, (int) ($request->getQueryParams()['days'] ?? 30)));

        return JsonResponder::success([
            'overview'  => $this->stats->overview(),
            'growth'    => $this->stats->growth($days),
            'top_users' => $this->stats->topUsers(10),
        ]);
    }

    public function health(): ResponseInterface
    {
        return JsonResponder::success($this->stats->systemHealth());
    }
}
