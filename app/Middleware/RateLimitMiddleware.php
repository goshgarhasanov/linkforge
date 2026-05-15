<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Http\JsonResponder;
use Predis\Client as RedisClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RedisClient $redis,
        private readonly int $maxRequests,
        private readonly int $windowSeconds = 60,
        private readonly string $bucket = 'default',
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identifier = $this->identifier($request);
        $key = "ratelimit:{$this->bucket}:{$identifier}";

        $current = (int) $this->redis->incr($key);

        if ($current === 1) {
            $this->redis->expire($key, $this->windowSeconds);
        }

        $ttl = (int) $this->redis->ttl($key);
        $remaining = max(0, $this->maxRequests - $current);

        if ($current > $this->maxRequests) {
            return JsonResponder::error(
                sprintf('Rate limit aşıldı. %d saniyə sonra yenidən cəhd edin.', $ttl),
                429,
            )
                ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
                ->withHeader('X-RateLimit-Remaining', '0')
                ->withHeader('Retry-After', (string) $ttl);
        }

        $response = $handler->handle($request);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) $remaining)
            ->withHeader('X-RateLimit-Reset', (string) (time() + $ttl));
    }

    private function identifier(ServerRequestInterface $request): string
    {
        $user = $request->getAttribute('user');
        if ($user !== null && property_exists($user, 'id')) {
            return 'user:' . $user->id;
        }

        $server = $request->getServerParams();
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (! empty($server[$key])) {
                return 'ip:' . trim(explode(',', $server[$key])[0]);
            }
        }

        return 'ip:unknown';
    }
}
