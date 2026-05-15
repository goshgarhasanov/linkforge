<?php

declare(strict_types=1);

namespace App\Support\Http;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

final class JsonResponder
{
    public static function success(mixed $data = null, int $status = 200, array $meta = []): ResponseInterface
    {
        $payload = ['success' => true];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return self::write(new Response($status), $payload);
    }

    public static function error(string $message, int $status = 400, array $errors = []): ResponseInterface
    {
        $payload = [
            'success' => false,
            'error'   => [
                'message' => $message,
                'code'    => $status,
            ],
        ];

        if ($errors !== []) {
            $payload['error']['fields'] = $errors;
        }

        return self::write(new Response($status), $payload);
    }

    public static function created(mixed $data, ?string $location = null): ResponseInterface
    {
        $response = self::success($data, 201);

        if ($location !== null) {
            $response = $response->withHeader('Location', $location);
        }

        return $response;
    }

    public static function noContent(): ResponseInterface
    {
        return new Response(204);
    }

    private static function write(ResponseInterface $response, array $payload): ResponseInterface
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('X-LinkForge-Version', '1.0.0');
    }
}
