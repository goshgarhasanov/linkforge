# Changelog

Bütün diqqətəlayiq dəyişikliklər bu faylda qeyd ediləcək.

Format [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) standartına əsaslanır.
Bu layihə [Semantic Versioning](https://semver.org/spec/v2.0.0.html) istifadə edir.

## [Unreleased]

### Phase 7 — Real-time Updates & Notifications (2026-05-15)

#### Added
- `notifications` migration + `Notification` model (uuid, type, title, body, action_url, metadata, read_at)
- `NotificationService` — DB persistence + Redis Pub/Sub publish
- Server-Sent Events endpoint: `GET /api/v1/stream` — Redis pubsub `lf:user:{id}` channel
- 60s connection lifetime, 15s heartbeat ping, graceful shutdown on `connection_aborted`
- `NotificationController` — list, mark single read, mark all read

### Phase 6 — Subscription Plans & Stripe Billing (2026-05-15)

#### Added
- `subscriptions` + `billing_events` migrations
- `SubscriptionPlan` enum (Free/Pro/Enterprise) with prices, limits, features
- `Subscription` model with status (trialing/active/past_due/canceled/incomplete)
- `BillingService` — Stripe Checkout session creation, signed webhook handling (HMAC-SHA256)
- Idempotent webhook processing (stripe_event_id unique constraint)
- Event handlers: `checkout.session.completed`, `customer.subscription.{created,updated,deleted}`
- `BillingController` — current plan, plans catalog, checkout init, webhook receiver
- LinkService quota now respects active subscription plan limits

### Phase 5 — Bulk CSV Import, Webhooks & Rate Limiting (2026-05-15)

#### Added
- `webhooks` + `webhook_deliveries` migrations
- `Webhook` model with event filtering (`listensTo()`), `WebhookDelivery` for delivery log
- `WebhookDispatcher` — HMAC-SHA256 signed deliveries (`X-LinkForge-Signature`), 5s timeout, auto-disable after 10 consecutive failures
- `WebhookController` — list, create (HTTPS-only), delete
- `BulkLinkImporter` — CSV parse (header detection), max 1000 rows, per-row error reporting
- `POST /api/v1/links/bulk` endpoint (JSON or multipart upload)
- `RateLimitMiddleware` — Redis-backed token bucket, configurable per-route bucket, `X-RateLimit-*` headers, `Retry-After` on 429

### Phase 4 — OAuth2, 2FA & Email Verification (2026-05-15)

#### Added
- `oauth_accounts` migration + `OAuthAccount` model
- `OAuthService` — provider abstraction (Google + GitHub) via `league/oauth2-client`
- `/auth/oauth/{provider}` redirect + `/auth/oauth/{provider}/callback` handlers
- State CSRF protection (HttpOnly cookie + state param comparison)
- Auto-link existing accounts by email; auto-create with verified status
- `TwoFactorService` — TOTP via `robthree/twofactorauth`, QR code data URI
- `POST /auth/2fa/begin` → generate secret + QR; `confirm` → enable; `disable` → revoke
- `email_verifications` migration with hashed tokens, 24h TTL
- `EmailVerificationService` — Symfony Mailer integration (graceful no-op if not configured)
- `/verify-email?token=` endpoint with custom success/failure page

### Phase 3 — Admin Panel, Moderation & Audit (2026-05-15)

#### Added
- `audit_logs` cədvəli + `AuditAction` enum (13 əməliyyat növü) + `AuditLogger` service
- `AdminMiddleware` — `UserRole::canAccessAdmin()` ilə icazə yoxlanması
- `WebAuthMiddleware` — cookie/header bearer parse (gələcək web auth üçün)
- `/admin` — sistem icmalı: 3 stat group (users/links/clicks), 30-day growth chart, top 10 user cədvəli
- `/admin/users` — istifadəçi siyahısı: search, role filter, status filter, role dəyişdirmə (inline select), ban/unban
- `/admin/links` — link moderasiyası: flag/unflag (səbəb ilə), deaktivasiya, sistem-level silmə
- `/admin/audit` — audit log: action filter, pagination, metadata göstərmək
- `/admin/health` — DB/Redis/disk/PHP health check, latency göstərici, 15s avtomatik yenilənmə, real-time monitoring
- `AdminStatsService` — overview aggregations, growth timeseries, system health probes
- `AdminApiController`, `UserAdminController`, `LinkAdminController`, `AuditLogController`
- 13 yeni admin API endpoint-i (hamısı admin middleware altında, audit-logged)
- Self-protection: aktor öz rolunu dəyişdirə bilmir, super admin yalnız super admin tərəfindən
- `database/seed.php` — idempotent admin user yaratmaq scripti

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
