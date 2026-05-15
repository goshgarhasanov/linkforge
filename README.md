<div align="center">

# 🔗 LinkForge

### Peşəkar URL Qısaltma və Analitika Platforması

[![CI](https://github.com/goshgarhasanov/linkforge/actions/workflows/ci.yml/badge.svg)](https://github.com/goshgarhasanov/linkforge/actions)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHPStan Level 8](https://img.shields.io/badge/PHPStan-Level%208-blueviolet)](phpstan.neon)

**Bit.ly səviyyəsində URL qısaltma. Tam analitika. Sənaye səviyyəli arxitektura.**

[Demo](#) · [Sənədləşmə](#-sənədləşmə) · [API](#-api) · [Töhfə vermək](CONTRIBUTING.md)

</div>

---

## ✨ Xüsusiyyətlər

- 🔗 **URL Qısaltma** — Base62 kodlaşdırma, xüsusi alias dəstəyi
- 📊 **Real Vaxt Analitika** — Coğrafiya, cihaz, brauzer, referrer statistikası
- 🔒 **Şifrəli Linklər** — Argon2id hash ilə qorunan linklər
- ⏰ **Müddət və Limit** — Bitmə tarixi və maksimum klik sayı
- 📱 **QR Kod** — PNG və SVG formatda yüksək keyfiyyətli generasiya
- 🎯 **Deep Linking** — iOS və Android tətbiqlərinə avtomatik yönləndirmə
- 🚀 **RESTful API** — OpenAPI 3.1 sənədləşməsi, JWT autentifikasiya
- 🛡️ **Bot Aşkarlanması** — Şübhəli klikləri statistikadan kənarlaşdırma
- 🌍 **GeoIP** — MaxMind ilə coğrafi məlumat
- 📦 **Toplu İdxal** — CSV faylından minlərlə link

## 🏗️ Texniki Stack

| Sahə | Texnologiya |
|------|-------------|
| **Backend** | PHP 8.3, Slim Framework 4, PHP-DI |
| **Database** | MySQL 8, Eloquent ORM, Redis |
| **Frontend** | Twig, TailwindCSS, Alpine.js, Chart.js |
| **Autentifikasiya** | JWT (Firebase), Argon2id, TOTP 2FA |
| **DevOps** | Docker, Docker Compose, GitHub Actions |
| **Keyfiyyət** | PHPStan Level 8, PHPUnit, PHP-CS-Fixer |

## 🚀 Sürətli Başlanğıc

### Tələblər

- Docker və Docker Compose
- Git
- ~2 GB boş disk sahəsi

### Quraşdırma

```bash
# 1. Repository-ni klonlayın
git clone https://github.com/goshgarhasanov/linkforge.git
cd linkforge

# 2. Mühit faylını hazırlayın
cp .env.example .env

# 3. Docker konteynerlərini işə salın
docker compose up -d

# 4. Asılılıqları quraşdırın
docker compose exec php composer install

# 5. Migration-ları işə salın
docker compose exec php php database/migrate.php

# 6. Brauzeri açın
open http://localhost:8080
```

Demo hazırdır! `http://localhost:8080` ünvanından landing səhifəsinə daxil olun.

## 📡 API

### Sağlamlıq yoxlaması

```bash
curl http://localhost:8080/api/v1/health
```

### Qeydiyyat

```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Qoshqar Hasanov",
    "email": "qoshqar@example.com",
    "password": "GucluSifre123"
  }'
```

### Giriş

```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "qoshqar@example.com",
    "password": "GucluSifre123"
  }'
```

### Link yaratmaq

```bash
curl -X POST http://localhost:8080/api/v1/links \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com/cox-uzun-link",
    "alias": "menim-linkim",
    "title": "Mənim layihəm",
    "expires_at": "2026-12-31T23:59:59Z"
  }'
```

### Bütün endpoint-lər

| Method | Endpoint | Təsvir | Auth |
|--------|----------|--------|------|
| `GET`    | `/api/v1/health`         | Servisin sağlamlıq statusu | ❌ |
| `POST`   | `/api/v1/auth/register`  | Yeni istifadəçi qeydiyyatı | ❌ |
| `POST`   | `/api/v1/auth/login`     | Giriş və JWT token | ❌ |
| `GET`    | `/api/v1/auth/me`        | Cari istifadəçi profili | ✅ |
| `POST`   | `/api/v1/links`          | Yeni link yarat | ✅ |
| `GET`    | `/api/v1/links`          | Linklərin siyahısı (pagination) | ✅ |
| `GET`    | `/api/v1/links/{code}`   | Link detalları | ✅ |
| `DELETE` | `/api/v1/links/{code}`   | Link sil | ✅ |
| `GET`    | `/api/v1/links/{code}/analytics` | Analitika məlumatları | ✅ |
| `GET`    | `/api/v1/links/{code}/qr.png`    | PNG formatında QR kod | ❌ |
| `GET`    | `/api/v1/links/{code}/qr.svg`    | SVG formatında QR kod | ❌ |
| `PATCH`  | `/api/v1/settings/profile`       | Profil yenilə | ✅ |
| `POST`   | `/api/v1/settings/password`      | Şifrə dəyiş | ✅ |
| `GET`    | `/api/v1/settings/tokens`        | API tokenlərin siyahısı | ✅ |
| `POST`   | `/api/v1/settings/tokens`        | Yeni API token | ✅ |
| `DELETE` | `/api/v1/settings/tokens/{id}`   | Tokeni ləğv et | ✅ |

📖 **Tam interaktiv sənədləşmə:** [`http://localhost:8080/docs`](http://localhost:8080/docs) (Swagger UI)

## 🏛️ Arxitektura

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
                         ┌─────────────────────┴─────────┐
                         ▼                               ▼
                 ┌──────────────┐                ┌──────────────┐
                 │  MySQL 8     │                │   Redis 7    │
                 │  (data)      │                │ (cache/rate) │
                 └──────────────┘                └──────────────┘
```

### Qovluq strukturu

```
linkforge/
├── app/
│   ├── Controllers/      # HTTP request handlers (Api, Web, Admin)
│   ├── Models/           # Eloquent modelləri
│   ├── Services/         # Biznes məntiqi
│   ├── Repositories/     # Verilənlər bazası abstraksiyası
│   ├── Middleware/       # Auth, rate limit, CORS
│   ├── Validators/       # Daxil olan məlumatların yoxlanması
│   ├── DTO/              # Data Transfer Object-lər
│   ├── Enums/            # Tip-təhlükəsiz enumerasiya
│   └── Support/          # Application, exceptions, helpers
├── config/               # Konfiqurasiya faylları
├── database/migrations/  # Schema migrasiyaları
├── public/               # Web kökü (index.php)
├── resources/views/      # Twig şablonları
├── routes/               # API və web marşrutları
├── tests/                # Unit, Feature, Integration
└── docker/               # Docker konfiqurasiyaları
```

## 🧪 Test və Keyfiyyət

```bash
# Bütün testlər
docker compose exec php composer test

# Code coverage
docker compose exec php composer test:coverage

# Statik analiz (PHPStan Level 8)
docker compose exec php composer analyse

# Code style (PSR-12)
docker compose exec php composer format
```

## 🛡️ Təhlükəsizlik

- ✅ **Argon2id** şifrə hash-i
- ✅ **JWT** (HS256) ilə autentifikasiya
- ✅ **CSRF** qoruması (web formaları üçün)
- ✅ **SQL Injection** — yalnız prepared statements
- ✅ **XSS** — Twig auto-escape, CSP başlıqları
- ✅ **Rate Limiting** — Token bucket alqoritmi (Redis)
- ✅ **Login Lockout** — 5 səhv → 15 dəqiqə blok
- ✅ **IP Anonimləşdirmə** — GDPR uyğunluğu üçün
- ✅ **HSTS, X-Frame-Options, X-Content-Type-Options** başlıqları

## 📋 Roadmap

- [x] **Phase 1** — Foundation, auth, link CRUD, redirect, analytics tracking
- [x] **Phase 2** — Dashboard UI, charts, link management, QR codes, OpenAPI/Swagger
- [x] **Phase 3** — Admin panel, user management, link moderation, audit log, system health
- [ ] **Phase 4** — Toplu idxal, QR kod, deep linking
- [ ] **Phase 5** — OAuth2 (Google, GitHub), 2FA
- [ ] **Phase 6** — Premium planlar, billing integration
- [ ] **Phase 7** — Webhook-lar, real-time updates (WebSocket)
- [ ] **Phase 8** — Mobil tətbiq (React Native)

## 🤝 Töhfə vermək

Töhfələr çox xoş gəlir! Lütfən [CONTRIBUTING.md](CONTRIBUTING.md) faylına baxın.

1. Repository-ni fork edin
2. Feature branch-i yaradın (`git checkout -b feature/yeni-funksiya`)
3. Dəyişikliklərinizi commit edin (`git commit -m 'feat: yeni funksiya əlavə edildi'`)
4. Branch-ı push edin (`git push origin feature/yeni-funksiya`)
5. Pull Request açın

**Commit qaydaları:** [Conventional Commits](https://www.conventionalcommits.org/)

## 📜 Lisenziya

Bu layihə **MIT** lisenziyası altında lisenziyalaşdırılıb — ətraflı məlumat üçün [LICENSE](LICENSE) faylına baxın.

## 👤 Müəllif

**Qoshqar Hasanov**

- GitHub: [@goshgarhasanov](https://github.com/goshgarhasanov)
- Email: hasnaov@gmail.com

---

<div align="center">

⭐ **Layihəni bəyəndinizsə, ulduz verməyi unutmayın!** ⭐

Bakıda ❤ ilə hazırlanıb.

</div>
