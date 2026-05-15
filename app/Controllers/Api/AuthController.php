<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\AuthService;
use App\Support\Exceptions\HttpException;
use App\Support\Exceptions\ValidationException;
use App\Support\Http\JsonResponder;
use App\Validators\AuthValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly AuthValidator $validator,
    ) {
    }

    public function register(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        try {
            $data = $this->validator->validateRegister($body);
            $user = $this->auth->register($data['name'], $data['email'], $data['password']);
        } catch (ValidationException $e) {
            return JsonResponder::error($e->getMessage(), 422, $e->errors);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::created([
            'user' => [
                'uuid'  => $user->uuid,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role->value,
            ],
        ]);
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        try {
            $data = $this->validator->validateLogin($body);
            $tokens = $this->auth->login(
                email: $data['email'],
                password: $data['password'],
                ipAddress: $this->clientIp($request),
                userAgent: $request->getHeaderLine('User-Agent') ?: null,
            );
        } catch (ValidationException $e) {
            return JsonResponder::error($e->getMessage(), 422, $e->errors);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::success($tokens);
    }

    public function me(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');

        return JsonResponder::success([
            'uuid'      => $user->uuid,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role->value,
            'avatar'    => $user->avatar_url,
            'verified'  => $user->isEmailVerified(),
            'two_factor' => $user->two_factor_enabled,
        ]);
    }

    private function clientIp(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();

        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (! empty($server[$key])) {
                return trim(explode(',', $server[$key])[0]);
            }
        }

        return '0.0.0.0';
    }
}
