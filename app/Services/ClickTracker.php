<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DeviceType;
use App\Models\Click;
use App\Models\Link;
use donatj\UserAgent\UserAgentParser;
use Psr\Http\Message\ServerRequestInterface;

final class ClickTracker
{
    private const BOT_PATTERNS = [
        'bot', 'crawler', 'spider', 'slurp', 'mediapartners',
        'facebookexternalhit', 'whatsapp', 'telegram', 'twitterbot',
        'discordbot', 'linkedinbot', 'preview',
    ];

    public function track(Link $link, ServerRequestInterface $request): Click
    {
        $userAgent = $request->getHeaderLine('User-Agent') ?: '';
        $ip = $this->clientIp($request);
        $referrerUrl = $request->getHeaderLine('Referer') ?: null;
        $language = $this->parseLanguage($request->getHeaderLine('Accept-Language'));

        $isBot = $this->detectBot($userAgent);
        $deviceType = $this->detectDevice($userAgent, $isBot);
        $parsed = $this->parseUserAgent($userAgent);

        $visitorHash = $this->visitorHash($ip, $userAgent, $link->id);
        $isUnique = ! Click::query()
            ->where('link_id', $link->id)
            ->where('visitor_hash', $visitorHash)
            ->exists();

        $click = new Click([
            'link_id'         => $link->id,
            'visitor_hash'    => $visitorHash,
            'ip_address'      => $this->anonymizeIp($ip),
            'device_type'     => $deviceType,
            'browser'         => $parsed['browser'],
            'browser_version' => $parsed['browser_version'],
            'os'              => $parsed['os'],
            'user_agent'      => mb_substr($userAgent, 0, 500),
            'referrer_host'   => $referrerUrl !== null ? parse_url($referrerUrl, PHP_URL_HOST) : null,
            'referrer_url'    => $referrerUrl,
            'language'        => $language,
            'is_unique'       => $isUnique,
            'is_bot'          => $isBot,
            'clicked_at'      => now(),
        ]);
        $click->save();

        if (! $isBot) {
            $link->increment('click_count');
            if ($isUnique) {
                $link->increment('unique_click_count');
            }
        }

        return $click;
    }

    private function detectBot(string $userAgent): bool
    {
        $lower = strtolower($userAgent);
        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function detectDevice(string $userAgent, bool $isBot): DeviceType
    {
        if ($isBot) {
            return DeviceType::Bot;
        }

        $lower = strtolower($userAgent);

        if (str_contains($lower, 'ipad') || str_contains($lower, 'tablet')) {
            return DeviceType::Tablet;
        }

        if (preg_match('/(android|iphone|ipod|mobile|blackberry|opera mini)/', $lower) === 1) {
            return DeviceType::Mobile;
        }

        if ($lower === '') {
            return DeviceType::Unknown;
        }

        return DeviceType::Desktop;
    }

    /**
     * @return array{browser: ?string, browser_version: ?string, os: ?string}
     */
    private function parseUserAgent(string $userAgent): array
    {
        if ($userAgent === '') {
            return ['browser' => null, 'browser_version' => null, 'os' => null];
        }

        try {
            $parsed = (new UserAgentParser())->parse($userAgent);

            return [
                'browser'         => $parsed->browser() ?: null,
                'browser_version' => $parsed->browserVersion() ?: null,
                'os'              => $parsed->platform() ?: null,
            ];
        } catch (\Throwable) {
            return ['browser' => null, 'browser_version' => null, 'os' => null];
        }
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

    private function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';

            return implode('.', $parts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);

            return implode(':', array_slice($parts, 0, 4)) . '::';
        }

        return $ip;
    }

    private function visitorHash(string $ip, string $userAgent, int $linkId): string
    {
        $salt = $_ENV['APP_KEY'] ?? 'linkforge';

        return hash('sha256', $ip . '|' . $userAgent . '|' . $linkId . '|' . $salt);
    }

    private function parseLanguage(string $header): ?string
    {
        if ($header === '') {
            return null;
        }

        $first = explode(',', $header)[0];

        return mb_substr(trim(explode(';', $first)[0]), 0, 10) ?: null;
    }
}
