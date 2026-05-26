<div align="center">

<a href="https://github.com/goshgarhasanov/linkforge">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&customColorList=6,11,20&height=200&section=header&text=LinkForge&fontSize=70&fontColor=ffffff&animation=fadeIn&fontAlignY=38&desc=Professional%20URL%20Shortener%20%26%20Analytics%20Platform&descAlignY=60&descSize=18" alt="LinkForge" />
</a>

<p>
  <b>🌍 Language / Dil:</b> &nbsp;
  <a href="README.md"><img src="https://img.shields.io/badge/English-0066CC?style=for-the-badge&logo=googletranslate&logoColor=white" /></a>
  <a href="README.az.md"><img src="https://img.shields.io/badge/Azərbaycanca-EF3340?style=for-the-badge&logo=googletranslate&logoColor=white" /></a>
</p>

<p>
  <a href="https://github.com/goshgarhasanov/linkforge/actions">
    <img src="https://img.shields.io/github/actions/workflow/status/goshgarhasanov/linkforge/ci.yml?branch=main&style=flat-square&logo=githubactions&logoColor=white&label=CI&color=22c55e" alt="CI" />
  </a>
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Slim-4.14-7B68EE?style=flat-square" alt="Slim" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Redis-7-DC382D?style=flat-square&logo=redis&logoColor=white" alt="Redis" />
  <img src="https://img.shields.io/badge/Docker-ready-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/PHPStan-Level%208-8A2BE2?style=flat-square" alt="PHPStan" />
  <img src="https://img.shields.io/badge/i18n-EN%20%2B%20AZ-orange?style=flat-square" alt="i18n" />
  <img src="https://img.shields.io/badge/License-MIT-22c55e?style=flat-square" alt="MIT" />
</p>

<p>
  <a href="#-quick-start">Quick Start</a> •
  <a href="#-features">Features</a> •
  <a href="#-api">API</a> •
  <a href="#-architecture">Architecture</a> •
  <a href="#-security">Security</a> •
  <a href="CONTRIBUTING.md">Contributing</a>
</p>

<br>

<img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=20&duration=3000&pause=1000&color=6366F1&center=true&vCenter=true&multiline=true&width=600&height=80&lines=Bit.ly-grade+URL+shortening.;Real-time+analytics.;Production-ready+architecture." alt="Typing SVG" />

</div>

---

## 🌟 What is LinkForge?

> **LinkForge** is an enterprise-grade URL shortener built with **PHP 8.3** and modern best practices. Think **Bit.ly + Rebrandly**, but **open-source**, **self-hostable**, and **production-ready**. The interface is fully bilingual: **English** and **Azerbaijani**.

<table>
<tr>
<td width="33%" align="center">

### 🔗 Shorten
Convert any long URL into a beautiful short link with custom aliases, expiration dates, and password protection.

</td>
<td width="33%" align="center">

### 📊 Analyze
Track every click with real-time geography, device, browser, and referrer breakdowns. Built-in bot filtering.

</td>
<td width="33%" align="center">

### 🚀 Scale
REST API, webhooks, Stripe billing, OAuth2, 2FA — everything you need to build a SaaS on top.

</td>
</tr>
</table>

---

## ✨ Features

<div align="center">

| 🔗 Core | 📊 Analytics | 🛡️ Security | 💼 Enterprise |
|:--------|:------------|:------------|:--------------|
| ✅ Custom alias | ✅ Geo breakdown | ✅ Argon2id | ✅ OAuth2 (Google/GitHub) |
| ✅ Password protection | ✅ Device tracking | ✅ JWT (HS256) | ✅ TOTP 2FA |
| ✅ Expiration dates | ✅ Referrer sources | ✅ Rate limiting | ✅ Email verification |
| ✅ Click limits | ✅ Browser stats | ✅ CSRF protection | ✅ Webhooks (HMAC) |
| ✅ QR codes (PNG/SVG) | ✅ Hourly heatmap | ✅ IP anonymization | ✅ Stripe billing |
| ✅ Deep linking | ✅ Time-series charts | ✅ Bot detection | ✅ Server-Sent Events |
| ✅ UTM auto-append | ✅ CSV export | ✅ Login lockout | ✅ Admin panel |
| ✅ Bulk CSV import | ✅ Real-time updates | ✅ HSTS, CSP | ✅ Audit log |
| ✅ **Bilingual UI** | ✅ **EN + AZ everywhere** | ✅ Per-user locale | ✅ i18n built-in |

</div>

---

## 🌍 Bilingual Interface

> The entire user interface — landing page, dashboard, admin panel, error pages, and emails — is available in **English** and **Azerbaijani**.

- 🇬🇧 **English** — default for international users
- 🇦🇿 **Azərbaycanca** — full native translation
- 🔁 Language switcher available in the top navigation bar
- 💾 Selected language is saved per user (in profile) and per browser (cookie)
- 📁 Translation files: `resources/lang/en.php` and `resources/lang/az.php`
- 🎯 Twig helper: `{{ t('dashboard.welcome') }}`

---

## 🚀 Quick Start

> **Get LinkForge running in under 2 minutes.** Requires Docker.

```bash
# 1️⃣ Clone
git clone https://github.com/goshgarhasanov/linkforge.git
cd linkforge

# 2️⃣ Configure
cp .env.example .env

# 3️⃣ Spin up
docker compose up -d

# 4️⃣ Install & migrate
docker compose exec php composer install
docker compose exec php php database/migrate.php
docker compose exec php php database/seed.php

# 5️⃣ Open browser
# → http://localhost:8080          (landing page)
# → http://localhost:8080/docs     (Swagger UI)
# → http://localhost:8080/admin    (admin@linkforge.io / Admin12345)
```

> ⚠️ **Production note:** Change the default admin password immediately and set strong values for `APP_KEY`, `JWT_SECRET`, and Stripe keys in `.env`.

---

## 🏗️ Tech Stack

<div align="center">

### Backend
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Slim](https://img.shields.io/badge/Slim_Framework-4-7B68EE?style=for-the-badge)
![Composer](https://img.shields.io/badge/Composer-885630?style=for-the-badge&logo=composer&logoColor=white)
![PHP-DI](https://img.shields.io/badge/PHP--DI-7-blue?style=for-the-badge)

### Data
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=for-the-badge&logo=redis&logoColor=white)
![Eloquent](https://img.shields.io/badge/Eloquent_ORM-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)

### Frontend
![Twig](https://img.shields.io/badge/Twig-3-95BF21?style=for-the-badge&logo=twig&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-3.4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-4-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)

### DevOps & Quality
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?style=for-the-badge&logo=githubactions&logoColor=white)
![PHPStan](https://img.shields.io/badge/PHPStan-Level_8-8A2BE2?style=for-the-badge)
![PHPUnit](https://img.shields.io/badge/PHPUnit-11.3-44A833?style=for-the-badge&logo=phpunit&logoColor=white)

### Integrations
![Stripe](https://img.shields.io/badge/Stripe-008CDD?style=for-the-badge&logo=stripe&logoColor=white)
![OAuth](https://img.shields.io/badge/OAuth2-Google%20%2B%20GitHub-181717?style=for-the-badge&logo=oauth&logoColor=white)
![JWT](https://img.shields.io/badge/JWT-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white)

</div>

---

## 📡 API

> Full interactive documentation: **`http://localhost:8080/docs`** (Swagger UI)

### 🔥 Example: Create a short link

```bash
curl -X POST http://localhost:8080/api/v1/links \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/very-long-url",
    "alias": "my-link",
    "title": "My portfolio",
    "expires_at": "2026-12-31T23:59:59Z",
    "max_clicks": 1000,
    "password": "secret123"
  }'
```

### 📚 Endpoint Reference

<details>
<summary><b>🔐 Authentication & Users</b> (8 endpoints)</summary>

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|:----:|
| `POST` | `/api/v1/auth/register` | Register new user | ❌ |
| `POST` | `/api/v1/auth/login` | Login and receive JWT | ❌ |
| `GET`  | `/api/v1/auth/me` | Current user profile | ✅ |
| `POST` | `/api/v1/auth/2fa/begin` | Start 2FA enrollment | ✅ |
| `POST` | `/api/v1/auth/2fa/confirm` | Confirm and enable 2FA | ✅ |
| `POST` | `/api/v1/auth/2fa/disable` | Disable 2FA | ✅ |
| `GET`  | `/auth/oauth/{provider}` | OAuth redirect (Google/GitHub) | ❌ |
| `GET`  | `/auth/oauth/{provider}/callback` | OAuth callback | ❌ |

</details>

<details>
<summary><b>🔗 Links & Analytics</b> (7 endpoints)</summary>

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|:----:|
| `POST`   | `/api/v1/links` | Create a new link | ✅ |
| `GET`    | `/api/v1/links` | List links (paginated, searchable) | ✅ |
| `POST`   | `/api/v1/links/bulk` | Bulk import via CSV (max 1000) | ✅ |
| `GET`    | `/api/v1/links/{code}` | Link details | ✅ |
| `DELETE` | `/api/v1/links/{code}` | Delete link (soft) | ✅ |
| `GET`    | `/api/v1/links/{code}/analytics` | Click analytics | ✅ |
| `GET`    | `/api/v1/links/{code}/qr.{png,svg}` | QR code | ❌ |

</details>

<details>
<summary><b>💳 Billing, Webhooks & Real-time</b> (9 endpoints)</summary>

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET`    | `/api/v1/billing/plans` | List subscription plans |
| `GET`    | `/api/v1/billing/current` | Current subscription |
| `POST`   | `/api/v1/billing/checkout` | Create Stripe Checkout |
| `POST`   | `/api/v1/billing/webhook` | Stripe webhook receiver |
| `GET`    | `/api/v1/webhooks` | List webhooks |
| `POST`   | `/api/v1/webhooks` | Create HMAC-signed webhook |
| `DELETE` | `/api/v1/webhooks/{id}` | Delete webhook |
| `GET`    | `/api/v1/notifications` | List notifications |
| `GET`    | `/api/v1/stream` | Server-Sent Events stream |

</details>

<details>
<summary><b>👮 Admin</b> (13 endpoints)</summary>

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET`    | `/api/v1/admin/overview` | System overview + growth |
| `GET`    | `/api/v1/admin/health` | DB/Redis/disk health |
| `GET`    | `/api/v1/admin/users` | List users (filter by role/status) |
| `PATCH`  | `/api/v1/admin/users/{uuid}/role` | Change user role |
| `PATCH`  | `/api/v1/admin/users/{uuid}/toggle-active` | Ban/unban user |
| `GET`    | `/api/v1/admin/links` | List all links (moderation) |
| `PATCH`  | `/api/v1/admin/links/{uuid}/flag` | Flag suspicious link |
| `PATCH`  | `/api/v1/admin/links/{uuid}/unflag` | Remove flag |
| `PATCH`  | `/api/v1/admin/links/{uuid}/toggle-active` | Activate/deactivate |
| `DELETE` | `/api/v1/admin/links/{uuid}` | Delete link (system) |
| `GET`    | `/api/v1/admin/audit` | Audit log (filterable) |

</details>

---

## 🏛️ Architecture

```mermaid
graph TB
    Client[🌐 Client Browser / API Consumer]
    Nginx[⚡ Nginx :80]
    PHP[🐘 PHP-FPM 8.3<br/>Slim Framework]

    subgraph "Application Layer"
        MW[🛡️ Middleware<br/>Auth · Admin · RateLimit]
        Controllers[🎯 Controllers<br/>Api · Web · Admin]
        Services[⚙️ Services<br/>15 services]
        Models[🧬 Eloquent Models<br/>10 entities]
    end

    MySQL[(🗄️ MySQL 8<br/>12 tables)]
    Redis[(⚡ Redis 7<br/>Cache · Rate · Pub/Sub)]
    Stripe[💳 Stripe API]
    OAuth[🔐 Google/GitHub OAuth]

    Client --> Nginx
    Nginx --> PHP
    PHP --> MW
    MW --> Controllers
    Controllers --> Services
    Services --> Models
    Models --> MySQL
    Services --> Redis
    Services --> Stripe
    Services --> OAuth

    style Client fill:#6366f1,color:#fff
    style Nginx fill:#10b981,color:#fff
    style PHP fill:#8b5cf6,color:#fff
    style MySQL fill:#4479A1,color:#fff
    style Redis fill:#DC382D,color:#fff
    style Stripe fill:#008CDD,color:#fff
    style OAuth fill:#333,color:#fff
```

### 📁 Project Structure

```
linkforge/
├── 📂 app/
│   ├── 🎯 Controllers/      # HTTP handlers (Api, Web, Admin)
│   ├── 🧬 Models/           # Eloquent models (10 entities)
│   ├── ⚙️  Services/        # Business logic (15 services)
│   ├── 🛡️  Middleware/      # Auth, Admin, RateLimit
│   ├── ✅ Validators/       # Input validation
│   ├── 📦 DTO/              # Data transfer objects
│   ├── 🏷️  Enums/           # Type-safe enumerations
│   └── 🔧 Support/          # Application, Translator, helpers
├── ⚙️  config/              # Configuration files
├── 🗄️  database/migrations/ # 12 schema migrations
├── 🌐 public/               # Web root + openapi.yaml
├── 🎨 resources/
│   ├── views/               # Twig templates (~25 views)
│   └── lang/                # 🌍 i18n: en.php, az.php
├── 🛣️  routes/              # API + web routes
├── 🧪 tests/                # Unit, Feature, Integration
└── 🐳 docker/               # Container configurations
```

---

## 🛡️ Security

> **Security is not an afterthought.** Every endpoint, every input, every secret.

<table>
<tr>
<th>🔐 Authentication</th>
<th>🚨 Attack Prevention</th>
<th>📋 Compliance</th>
</tr>
<tr>
<td valign="top">

- ✅ Argon2id password hashing
- ✅ JWT (HS256) tokens
- ✅ TOTP 2FA (Google Authenticator)
- ✅ OAuth2 state CSRF
- ✅ Login lockout (5 → 15 min)
- ✅ Email verification (24h TTL)

</td>
<td valign="top">

- ✅ SQL injection (prepared stmt)
- ✅ XSS (Twig auto-escape, CSP)
- ✅ CSRF (web forms)
- ✅ Rate limiting (Redis bucket)
- ✅ Bot detection
- ✅ HMAC webhook signatures

</td>
<td valign="top">

- ✅ IP anonymization (GDPR)
- ✅ HSTS header
- ✅ X-Frame-Options
- ✅ Referrer-Policy
- ✅ Audit log (all admin actions)
- ✅ Permissions-Policy

</td>
</tr>
</table>

---

## 🧪 Testing & Quality

```bash
# 🧪 Run all tests
docker compose exec php composer test

# 📊 Generate coverage report
docker compose exec php composer test:coverage

# 🔍 Static analysis (PHPStan Level 8)
docker compose exec php composer analyse

# ✨ Auto-fix code style (PSR-12)
docker compose exec php composer format
```

---

## 🗺️ Roadmap

<div align="center">

| Phase | Feature Set | Status |
|:-----:|:------------|:------:|
| **1** | Foundation, auth, link CRUD, redirect, click tracking | ✅ Done |
| **2** | Dashboard UI, charts, link management, QR codes, OpenAPI | ✅ Done |
| **3** | Admin panel, user management, moderation, audit log | ✅ Done |
| **4** | OAuth2 (Google/GitHub), 2FA (TOTP), email verification | ✅ Done |
| **5** | Bulk CSV import, webhooks (HMAC), rate limiting (Redis) | ✅ Done |
| **6** | Subscription plans, Stripe Checkout + webhooks | ✅ Done |
| **7** | Real-time updates (SSE), in-app notifications | ✅ Done |
| **8** | **Bilingual UI (English + Azerbaijani)** | ✅ Done |
| 9 | Mobile app (React Native) | 🚧 Future |
| 10 | Custom domains, white-label | 🚧 Future |

</div>

---

## 📊 Project Stats

<div align="center">

| 📦 | Value |
|---|---|
| **PHP files** | 65+ |
| **Twig templates** | 25+ |
| **Database migrations** | 12 tables |
| **Total lines of code** | ~8,500 |
| **API endpoints** | 45+ |
| **Services** | 15 |
| **Eloquent models** | 10 |
| **Supported languages** | English + Azerbaijani |

</div>

---

## 🤝 Contributing

Contributions are **very welcome**! 🎉

1. 🍴 Fork the repository
2. 🌿 Create a feature branch (`git checkout -b feature/amazing-feature`)
3. 💾 Commit using [Conventional Commits](https://www.conventionalcommits.org/) (`git commit -m 'feat: add amazing feature'`)
4. 🚀 Push (`git push origin feature/amazing-feature`)
5. 📬 Open a Pull Request

See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📜 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 👤 Author

<div align="center">

### **Qoshqar Hasanov**

<p>
  <a href="https://github.com/goshgarhasanov">
    <img src="https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white" />
  </a>
  <a href="mailto:hasnaov@gmail.com">
    <img src="https://img.shields.io/badge/Email-D14836?style=for-the-badge&logo=gmail&logoColor=white" />
  </a>
</p>

</div>

---

<div align="center">

<img src="https://capsule-render.vercel.app/api?type=waving&color=gradient&customColorList=6,11,20&height=120&section=footer" alt="footer" />

### ⭐ If you like this project, please give it a star!

**Crafted with ❤️ in Baku, Azerbaijan**

</div>

---

## ☕ Support

If this project is useful to you, you can support me with a coffee — thank you!

**[☕ kofe.al/goshgarhasanov](https://kofe.al/goshgarhasanov)**
