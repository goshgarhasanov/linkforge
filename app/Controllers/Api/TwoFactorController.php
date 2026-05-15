<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\TwoFactorService;
use App\Support\Exceptions\HttpException;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TwoFactorController
{
    public function __construct(
        private readonly TwoFactorService $tfa,
    ) {
    }

    public function begin(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        if ($user->two_factor_enabled) {
            return JsonResponder::error('2FA artıq aktivdir.', 409);
        }

        $data = $this->tfa->beginEnrollment($user);

        return JsonResponder::success($data);
    }

    public function confirm(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        try {
            $this->tfa->confirmEnrollment($user, (string) ($body['secret'] ?? ''), (string) ($body['code'] ?? ''));
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::success(['enabled' => true]);
    }

    public function disable(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        try {
            $this->tfa->disable($user, (string) ($body['code'] ?? ''));
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::success(['enabled' => false]);
    }
}
