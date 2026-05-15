# Changelog

Bütün diqqətəlayiq dəyişikliklər bu faylda qeyd ediləcək.

Format [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) standartına əsaslanır.
Bu layihə [Semantic Versioning](https://semver.org/spec/v2.0.0.html) istifadə edir.

## [Unreleased]

### Phase 1 — Foundation (2026-05-15)

#### Added
- Slim Framework 4 üzərində layihə skeleti
- Docker mühit (PHP 8.3, Nginx, MySQL 8, Redis 7, MailHog)
- Database migration sistemi və ilkin schema (users, links, clicks, api_tokens, password_resets, login_attempts)
- Eloquent modelləri tip-təhlükəsiz enum-larla (UserRole, DeviceType)
- JWT (HS256) əsaslı autentifikasiya sistemi
- Login lockout (5 səhv cəhd → 15 dəq blok)
- POST/GET/DELETE `/api/v1/links` endpoint-ləri
- Public redirect (`GET /{code}`) klik tracking ilə
- UTM parametrlərinin avtomatik əlavə edilməsi
- iOS/Android deep link dəstəyi
- Şifrəli linklər (Argon2id hash)
- Müddət (expires_at) və klik limiti (max_clicks)
- Bot aşkarlama və cihaz təsnifatı
- IP anonimləşdirmə (GDPR uyğunluğu)
- Twig + TailwindCSS landing səhifəsi (responsive, dark mode)
- Custom 404 / 410 / 500 səhifələri
- GitHub Actions CI pipeline (PHP 8.3 və 8.4 matrix-i)
- PHPStan Level 8, PHPUnit 11.3, PHP-CS-Fixer
- README.md (portfolio-grade), CONTRIBUTING.md, LICENSE (MIT)

## [1.0.0] — Hələ buraxılmayıb

İlkin stabil buraxılış.
