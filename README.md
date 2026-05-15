<div align="center">

**🌍 [English](README.md) · [Azərbaycanca](README.az.md)**

# 🔗 LinkForge

### Professional URL Shortener & Analytics Platform

[![CI](https://github.com/goshgarhasanov/linkforge/actions/workflows/ci.yml/badge.svg)](https://github.com/goshgarhasanov/linkforge/actions)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHPStan Level 8](https://img.shields.io/badge/PHPStan-Level%208-blueviolet)](phpstan.neon)

**Bit.ly-grade URL shortening. Full analytics. Production-ready architecture.**

[Demo](#) · [Documentation](#-api) · [API Reference](http://localhost:8080/docs) · [Contributing](CONTRIBUTING.md)

</div>

---

## ✨ Features

- 🔗 **URL Shortening** — Base62 encoding, custom alias support
- 📊 **Real-time Analytics** — Geography, device, browser, referrer breakdown
- 🔒 **Password-protected Links** — Argon2id hashed link passwords
- ⏰ **Expiration & Click Limits** — Auto-deactivate by date or click count
- 📱 **QR Code Generation** — High-quality PNG and SVG export
- 🎯 **Deep Linking** — Auto-routing for iOS/Android apps
- 🚀 **RESTful API** — OpenAPI 3.1 spec, JWT authentication
- 🛡️ **Bot Detection** — Suspicious clicks filtered from analytics
- 🌍 **GeoIP** — Country/city resolution via MaxMind
- 📦 **Bulk CSV Import** — Up to 1000 links per request
- 🔐 **OAuth2** — Sign in with Google or GitHub
- 🛡️ **Two-Factor Authentication** — TOTP-based (Google Authenticator)
- 📧 **Email Verification** — Token-based with 24h TTL
- 🪝 **Webhooks** — HMAC-signed event delivery
- 💳 **Stripe Billing** — Subscription plans with auto-enforcement
- ⚡ **Real-time Updates** — Server-Sent Events for live dashboard
- 👮 **Admin Panel** — User management, moderation, audit log, health monitoring

## 🏗️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.3, Slim Framework 4, PHP-DI |
| **Database** | MySQL 8, Eloquent ORM, Redis (cache + rate limiting + pub/sub) |
| **Frontend** | Twig, TailwindCSS, Alpine.js, Chart.js |
| **Authentication** | JWT (Firebase), Argon2id, TOTP 2FA, OAuth2 (Google/GitHub) |
| **Billing** | Stripe Checkout + Webhooks |
| **DevOps** | Docker, Docker Compose, GitHub Actions |
| **Quality** | PHPStan Level 8, PHPUnit 11, PHP-CS-Fixer |

## 🚀 Quick Start

### Requirements

- Docker & Docker Compose
- Git
- ~2 GB free disk space

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/goshgarhasanov/linkforge.git
cd linkforge

# 2. Prepare environment file
cp .env.example .env

# 3. Start Docker containers
docker compose up -d

# 4. Install dependencies
docker compose exec php composer install

# 5. Run migrations
docker compose exec php php database/migrate.php

# 6. Create admin user
docker compose exec php php database/seed.php

# 7. Open browser
open http://localhost:8080
```

**Default admin credentials:** `admin@linkforge.io` / `Admin12345` (change immediately in production!)

## 📡 API

Full interactive documentation available at `http://localhost:8080/docs` (Swagger UI).

### Health check

```bash
curl http://localhost:8080/api/v1/health
```

### Register & login

```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"SecurePass123"}'

curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"SecurePass123"}'
```

### Create a short link

```bash
curl -X POST http://localhost:8080/api/v1/links \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/very-long-url",
    "alias": "my-link",
    "title": "My portfolio",
    "expires_at": "2026-12-31T23:59:59Z"
  }'
```

### Endpoint Reference

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| `GET`    | `/api/v1/health`                       | Service health status | ❌ |
| `POST`   | `/api/v1/auth/register`                | Register new user | ❌ |
| `POST`   | `/api/v1/auth/login`                   | Login and receive JWT | ❌ |
| `GET`    | `/api/v1/auth/me`                      | Current user profile | ✅ |
| `POST`   | `/api/v1/auth/2fa/begin`               | Start 2FA enrollment | ✅ |
| `POST`   | `/api/v1/auth/2fa/confirm`             | Confirm and enable 2FA | ✅ |
| `POST`   | `/api/v1/auth/2fa/disable`             | Disable 2FA | ✅ |
| `POST`   | `/api/v1/links`                        | Create a new link | ✅ |
| `GET`    | `/api/v1/links`                        | List links (paginated) | ✅ |
| `POST`   | `/api/v1/links/bulk`                   | Bulk import via CSV | ✅ |
| `GET`    | `/api/v1/links/{code}`                 | Link details | ✅ |
| `DELETE` | `/api/v1/links/{code}`                 | Delete link | ✅ |
| `GET`    | `/api/v1/links/{code}/analytics`       | Click analytics | ✅ |
| `GET`    | `/api/v1/links/{code}/qr.{png,svg}`    | QR code | ❌ |
| `GET`    | `/api/v1/webhooks`                     | List webhooks | ✅ |
| `POST`   | `/api/v1/webhooks`                     | Create webhook | ✅ |
| `DELETE` | `/api/v1/webhooks/{id}`                | Delete webhook | ✅ |
| `GET`    | `/api/v1/billing/plans`                | List subscription plans | ❌ |
| `GET`    | `/api/v1/billing/current`              | Current subscription | ✅ |
| `POST`   | `/api/v1/billing/checkout`             | Create Stripe checkout | ✅ |
| `POST`   | `/api/v1/billing/webhook`              | Stripe webhook receiver | ❌ |
| `GET`    | `/api/v1/notifications`                | List notifications | ✅ |
| `PATCH`  | `/api/v1/notifications/read-all`       | Mark all as read | ✅ |
| `GET`    | `/api/v1/stream`                       | SSE event stream | ✅ |
| `GET`    | `/api/v1/admin/*`                      | Admin endpoints (13 total) | ✅ Admin |

## 🏛️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│                      Nginx (80)                          │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                  Slim 4 + PHP 8.3 (FPM)                  │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │ Controllers  │→ │   Services   │→ │ Repositories  │  │
│  └──────────────┘  └──────────────┘  └───────┬───────┘  │
│         ▲                                     │          │
│         │                                     ▼          │
│  ┌──────┴───────┐                    ┌───────────────┐  │
│  │  Middleware  │                    │   Eloquent    │  │
│  └──────────────┘                    └───────┬───────┘  │
└──────────────────────────────────────────────┼──────────┘
                                               │
                ┌──────────────┬───────────────┼──────────────┐
                ▼              ▼               ▼              ▼
        ┌──────────────┐ ┌──────────────┐ ┌─────────┐ ┌─────────────┐
        │  MySQL 8     │ │   Redis 7    │ │ Stripe  │ │ Google /    │
        │  (data)      │ │ (cache/SSE)  │ │ (billing)│ │ GitHub OAuth│
        └──────────────┘ └──────────────┘ └─────────┘ └─────────────┘
```

### Project structure

```
linkforge/
├── app/
│   ├── Controllers/      # HTTP handlers (Api, Web, Admin)
│   ├── Models/           # Eloquent models (10 entities)
│   ├── Services/         # Business logic (15 services)
│   ├── Middleware/       # Auth, Admin, RateLimit
│   ├── Validators/       # Input validation
│   ├── DTO/              # Data transfer objects
│   ├── Enums/            # Type-safe enumerations
│   └── Support/          # Application, exceptions, helpers
├── config/               # Configuration files
├── database/migrations/  # 12 schema migrations
├── public/               # Web root + openapi.yaml
├── resources/views/      # Twig templates (~25 views)
├── routes/               # API + web routes
├── tests/                # Unit, Feature, Integration
└── docker/               # Container configurations
```

## 🧪 Tests & Quality

```bash
# All tests
docker compose exec php composer test

# Coverage
docker compose exec php composer test:coverage

# Static analysis (PHPStan Level 8)
docker compose exec php composer analyse

# Code style (PSR-12)
docker compose exec php composer format
```

## 🛡️ Security

- ✅ **Argon2id** password hashing
- ✅ **JWT** (HS256) authentication
- ✅ **CSRF protection** (web forms)
- ✅ **SQL injection** prevention (prepared statements only)
- ✅ **XSS** protection (Twig auto-escape, CSP headers)
- ✅ **Rate limiting** (token bucket via Redis)
- ✅ **Login lockout** (5 failures → 15 min block)
- ✅ **IP anonymization** (GDPR compliance)
- ✅ **TOTP 2FA** (Google Authenticator compatible)
- ✅ **OAuth state** CSRF (HttpOnly cookie)
- ✅ **Webhook HMAC** signatures (SHA-256)
- ✅ **Stripe webhook** signature verification
- ✅ **HSTS, X-Frame-Options, Referrer-Policy** headers

## 📋 Roadmap

- [x] **Phase 1** — Foundation, auth, link CRUD, redirect, click tracking
- [x] **Phase 2** — Dashboard UI, charts, link management, QR codes, OpenAPI/Swagger
- [x] **Phase 3** — Admin panel, user management, moderation, audit log, system health
- [x] **Phase 4** — OAuth2 (Google/GitHub), 2FA (TOTP), email verification
- [x] **Phase 5** — Bulk CSV import, webhooks (HMAC-signed), rate limiting (Redis)
- [x] **Phase 6** — Subscription plans, Stripe Checkout + webhooks
- [x] **Phase 7** — Real-time updates via Server-Sent Events, notifications

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md).

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

**Commit convention:** [Conventional Commits](https://www.conventionalcommits.org/)

## 📜 License

This project is licensed under the **MIT** License — see the [LICENSE](LICENSE) file for details.

## 👤 Author

**Qoshqar Hasanov**

- GitHub: [@goshgarhasanov](https://github.com/goshgarhasanov)
- Email: hasnaov@gmail.com

---

<div align="center">

⭐ **If you like this project, please give it a star!** ⭐

Crafted with ❤ in Baku.

</div>
