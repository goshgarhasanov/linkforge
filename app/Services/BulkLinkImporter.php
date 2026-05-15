<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateLinkRequest;
use App\Models\User;

final class BulkLinkImporter
{
    private const MAX_ROWS = 1000;

    public function __construct(
        private readonly LinkService $links,
    ) {
    }

    /**
     * @return array{imported: int, failed: int, errors: array<int, string>}
     */
    public function importCsv(User $user, string $csvContent): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent)) ?: [];
        $imported = 0;
        $failed = 0;
        $errors = [];

        if (count($lines) > self::MAX_ROWS) {
            return ['imported' => 0, 'failed' => count($lines), 'errors' => ['CSV faylı maksimum ' . self::MAX_ROWS . ' sətir ehtiva edə bilər.']];
        }

        $header = str_getcsv((string) $lines[0]);
        $hasHeader = in_array('url', array_map('strtolower', $header), true);
        $startIdx = $hasHeader ? 1 : 0;

        $columns = $hasHeader ? array_map('strtolower', $header) : ['url', 'alias', 'title'];

        for ($i = $startIdx; $i < count($lines); $i++) {
            $row = str_getcsv((string) $lines[$i]);
            if ($row === [null] || $row === ['']) continue;

            $data = [];
            foreach ($columns as $idx => $col) {
                if (isset($row[$idx]) && $row[$idx] !== '') {
                    $data[$col] = $row[$idx];
                }
            }

            try {
                if (empty($data['url'])) {
                    throw new \InvalidArgumentException('URL boşdur');
                }

                $dto = new CreateLinkRequest(
                    originalUrl: $data['url'],
                    customAlias: $data['alias'] ?? null,
                    title: $data['title'] ?? null,
                    password: null,
                    expiresAt: null,
                    maxClicks: null,
                    utmParameters: null,
                    iosDeepLink: null,
                    androidDeepLink: null,
                );

                $this->links->create($dto, $user);
                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[$i + 1] = $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'failed'   => $failed,
            'errors'   => array_slice($errors, 0, 20, true),
        ];
    }
}
