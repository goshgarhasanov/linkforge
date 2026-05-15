# Changelog

Bütün diqqətəlayiq dəyişikliklər bu faylda qeyd ediləcək.

Format [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) standartına əsaslanır.
Bu layihə [Semantic Versioning](https://semver.org/spec/v2.0.0.html) istifadə edir.

## [Unreleased]

### Phase 2 — Dashboard, Analytics & API Docs (2026-05-15)

#### Added
- Auth UI: `/login` və `/register` səhifələri (split-screen, branding, error handling)
- Dashboard layout (sidebar + topbar, mobile responsive, dark mode toggle)
- `/dashboard` — KPI cards, son 30 günün klik qrafiki (Chart.js line chart), top 5 link
- `/dashboard/links` — link siyahısı (debounced search, pagination, copy-to-clipboard, sil)
- Yeni link yaratmaq modal-ı (URL, alias, password, expires, max_clicks, UTM, deep links)
- `/dashboard/links/{code}` — analitika dashboard-u:
  - KPI cards (toplam/unikal/24 saat/günlük orta)
  - Timeseries line chart (7/30/90 gün toggle)
  - Cihaz və brauzer doughnut chart-ları
  - Ölkə və referrer cədvəlləri
- `AnalyticsService` — summary, timeseries, breakdown, hourly heatmap
- `GET /api/v1/links/{code}/analytics` endpoint-i
- `GET /api/v1/links/{code}/qr.{png,svg}` — `endroid/qr-code` ilə QR generasiyası
- `/dashboard/settings` — profil, şifrə, API token CRUD UI
- `ApiTokenService` — sha256 hashed tokenlər, abilities-based authorization
- `PATCH /settings/profile`, `POST /settings/password`, `/settings/tokens` CRUD
- **OpenAPI 3.1 sənəd** (`/openapi.yaml`) + **Swagger UI** (`/docs`)

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
