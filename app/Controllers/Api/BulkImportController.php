<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\BulkLinkImporter;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BulkImportController
{
    public function __construct(
        private readonly BulkLinkImporter $importer,
    ) {
    }

    public function import(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        $csv = (string) ($body['csv'] ?? '');

        if ($csv === '') {
            $uploaded = $request->getUploadedFiles()['file'] ?? null;
            if ($uploaded !== null && $uploaded->getError() === UPLOAD_ERR_OK) {
                $csv = (string) $uploaded->getStream()->getContents();
            }
        }

        if (trim($csv) === '') {
            return JsonResponder::error('CSV məzmunu boşdur. "csv" sahəsi və ya "file" upload edin.', 422);
        }

        $result = $this->importer->importCsv($user, $csv);

        return JsonResponder::success($result);
    }
}
