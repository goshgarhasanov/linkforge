<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateLinkRequest;
use App\Models\Link;
use App\Models\User;
use App\Support\Exceptions\HttpException;

final class LinkService
{
    public function __construct(
        private readonly ShortCodeGenerator $codes,
    ) {
    }

    public function create(CreateLinkRequest $request, ?User $user = null): Link
    {
        $this->enforceUserQuota($user);

        $code = $request->customAlias !== null
            ? $this->codes->validateCustom($request->customAlias)
            : $this->codes->generate();

        $originalUrl = $this->appendUtm($request->originalUrl, $request->utmParameters);

        $link = new Link([
            'user_id'           => $user?->id,
            'short_code'        => $code,
            'original_url'      => $originalUrl,
            'title'             => $request->title,
            'password_hash'     => $request->password !== null
                ? password_hash($request->password, PASSWORD_ARGON2ID)
                : null,
            'expires_at'        => $request->expiresAt,
            'max_clicks'        => $request->maxClicks,
            'utm_parameters'    => $request->utmParameters,
            'ios_deep_link'     => $request->iosDeepLink,
            'android_deep_link' => $request->androidDeepLink,
            'is_active'         => true,
        ]);
        $link->save();

        return $link;
    }

    public function findByCode(string $code): Link
    {
        $link = Link::query()->where('short_code', $code)->first();

        if (! $link instanceof Link) {
            throw HttpException::notFound('Bu kodla heç bir link tapılmadı.');
        }

        return $link;
    }

    public function findOwnedByUser(int $linkId, User $user): Link
    {
        $link = Link::query()->where('id', $linkId)->where('user_id', $user->id)->first();

        if (! $link instanceof Link) {
            throw HttpException::notFound('Bu link tapılmadı.');
        }

        return $link;
    }

    private function enforceUserQuota(?User $user): void
    {
        if ($user === null) {
            return;
        }

        $limit = $this->resolveLimit($user);
        if ($limit === null) {
            return;
        }

        $current = Link::query()->where('user_id', $user->id)->count();

        if ($current >= $limit) {
            throw HttpException::forbidden(
                sprintf('Cari planınız üçün maksimum %d link limitinə çatmısınız.', $limit),
            );
        }
    }

    private function resolveLimit(User $user): ?int
    {
        if ($user->role->canAccessAdmin()) {
            return null;
        }

        $subscription = $user->subscriptions()->whereIn('status', ['trialing', 'active'])->latest()->first();

        if ($subscription !== null) {
            return $subscription->plan->linkLimit();
        }

        return $user->role->linkLimit();
    }

    /**
     * @param array<string, string>|null $utm
     */
    private function appendUtm(string $url, ?array $utm): string
    {
        if ($utm === null || $utm === []) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        parse_str($parts['query'] ?? '', $query);

        foreach ($utm as $key => $value) {
            $clean = preg_replace('/[^a-z0-9_]/i', '', (string) $key);
            if ($clean === '' || $clean === null) {
                continue;
            }
            $query['utm_' . strtolower($clean)] = (string) $value;
        }

        $parts['query'] = http_build_query($query);

        return $this->unparseUrl($parts);
    }

    /**
     * @param array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, path?: string, query?: string, fragment?: string} $parts
     */
    private function unparseUrl(array $parts): string
    {
        $scheme   = isset($parts['scheme'])   ? $parts['scheme'] . '://' : '';
        $host     = $parts['host']            ?? '';
        $port     = isset($parts['port'])     ? ':' . $parts['port']     : '';
        $user     = $parts['user']            ?? '';
        $pass     = isset($parts['pass'])     ? ':' . $parts['pass']     : '';
        $auth     = $user !== ''              ? $user . $pass . '@'      : '';
        $path     = $parts['path']            ?? '';
        $query    = isset($parts['query'])    ? '?' . $parts['query']    : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $auth . $host . $port . $path . $query . $fragment;
    }
}
