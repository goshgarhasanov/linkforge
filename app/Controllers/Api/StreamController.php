<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\User;
use Predis\Client as RedisClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

final class StreamController
{
    private const MAX_DURATION_SECONDS = 60;

    public function __construct(
        private readonly RedisClient $redis,
    ) {
    }

    public function subscribe(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        @set_time_limit(self::MAX_DURATION_SECONDS + 5);
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', '0');

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        $response = (new Response())
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache, no-transform')
            ->withHeader('X-Accel-Buffering', 'no')
            ->withHeader('Connection', 'keep-alive');

        $this->emit('hello', ['user' => $user->uuid, 'ts' => time()]);

        $deadline = time() + self::MAX_DURATION_SECONDS;
        $pubsub = $this->redis->pubSubLoop();
        $pubsub->subscribe('lf:user:' . $user->id);

        foreach ($pubsub as $message) {
            if (connection_aborted() || time() > $deadline) {
                $pubsub->unsubscribe();
                break;
            }

            if ($message->kind === 'message') {
                $decoded = json_decode((string) $message->payload, true);
                $this->emit($decoded['type'] ?? 'message', $decoded['payload'] ?? []);
            }

            if ((time() - ($deadline - self::MAX_DURATION_SECONDS)) % 15 === 0) {
                $this->emit('ping', ['ts' => time()]);
            }
        }

        return $response;
    }

    private function emit(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";

        if (function_exists('flush')) {
            @flush();
        }
    }
}
