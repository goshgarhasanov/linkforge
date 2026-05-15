# LinkForge-a töhfə vermək

Bu layihəyə töhfə vermək istədiyiniz üçün təşəkkür edirik! 🎉

## 📋 İnkişaf prosesi

### 1. Repository-ni hazırlayın

```bash
git clone https://github.com/goshgarhasanov/linkforge.git
cd linkforge
cp .env.example .env
docker compose up -d
docker compose exec php composer install
docker compose exec php php database/migrate.php
```

### 2. Yeni feature üzərində işləyin

```bash
git checkout -b feature/qisa-tesvir
```

Branch adlandırma qaydası:
- `feature/...` — yeni funksiyalar
- `fix/...` — xəta düzəlişləri
- `refactor/...` — kod yenidən təşkili
- `docs/...` — sənədləşmə dəyişiklikləri

### 3. Kod yazın

**Tələblər:**
- ✅ PSR-12 kod stili
- ✅ `declare(strict_types=1);` hər PHP faylında
- ✅ PHPStan Level 8 keçməlidir
- ✅ Yeni kod üçün test yazın (PHPUnit)
- ✅ Public API-lər üçün PHPDoc

### 4. Commit edin

[Conventional Commits](https://www.conventionalcommits.org/) standartına riayət edin:

```
feat: yeni xüsusi alias funksiyası əlavə edildi
fix: redirect zamanı şifrəli linkin yanlış işləməsi düzəldildi
docs: README-də API nümunələri yeniləndi
refactor: LinkService daha kiçik sinifələrə bölündü
test: ShortCodeGenerator üçün edge case testləri əlavə edildi
chore: composer paketləri yeniləndi
```

### 5. Test və lint işə salın

```bash
docker compose exec php composer test
docker compose exec php composer analyse
docker compose exec php composer format
```

### 6. Push və PR

```bash
git push origin feature/qisa-tesvir
```

GitHub-da Pull Request açın və şablonu doldurun.

## 🐛 Xəta hesabatları

[Issue](https://github.com/goshgarhasanov/linkforge/issues) açarkən aşağıdakıları daxil edin:

- **Təkrarlama addımları** (numerolaşdırılmış)
- **Gözlənilən davranış**
- **Faktiki davranış**
- **Screenshot / log** (mümkündürsə)
- **Mühit:** OS, PHP versiyası, Docker versiyası

## 💬 Sual və müzakirələr

[Discussions](https://github.com/goshgarhasanov/linkforge/discussions) bölməsindən istifadə edin.

## 📝 Code of Conduct

Bu layihə [Contributor Covenant](https://www.contributor-covenant.org/) Code of Conduct-a riayət edir. İştirak edərək bu qaydaları qəbul etmiş sayılırsınız.

---

Vaxtınız üçün təşəkkür edirik! ❤
