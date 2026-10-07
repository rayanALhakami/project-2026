# next trip — رحلتك القادمة وجميع فعالياتك في تطبيق واحد

مرشد سياحي ذكي للسياحة في المملكة العربية السعودية: يخطط الرحلات، يقترح الأماكن والمطاعم، يعرض الخرائط والاتجاهات، يترجم فورياً، يتعرّف على المعالم من الصور، ويوفّر معلومات الطقس والأسعار والمواقيت.

الرابط المباشر: <https://next-trip-production-wbbwxq.laravel.cloud>

---

## المزايا

### المتطلبات الأساسية (MVP)

- **مساعد ذكي محادث** — تخطيط رحلات، أسئلة عن الأماكن، توصيات، مقارنة، تقدير ميزانية، اتجاهات ومسافات، ترجمة، تعرّف على المعالم من الصور، طقس وأسعار
- **مساعد صوتي متعدد اللغات** — تفريغ صوتي (STT) ونطق (TTS) مع تبديل لغة مثل مترجم جوجل
- **أماكن سياحية** — كتالوج المدن والمعالم السعودية مع بحث/فلترة وخرائط Leaflet + OpenStreetMap
- **تعدد اللغات** — 10 لغات بواجهة RTL/LTR (العربية افتراضية)
- **ترجمة فورية** — صفحة ترجمة مستقلة + داخل المساعد
- **الطقس والأسعار** — Open-Meteo للأحوال الجوية، ومواقيت الصلاة من Aladhan، والأسعار من قاعدة البيانات
- **إشعارات ذكية** — تذكير الرحلة وتنبيهات الطقس والفعاليات القريبة (مجدولة يومياً 7:00 ص بتوقيت الرياض)

### إضافية

- **مخطط رحلة يوم بيوم قابل للتعديل** — إضافة مكان، حذف، ترتيب (أعلى/أسفل)، تعديل وقت البداية، تعليم كمزار
- **مشاركة وتصدير** — رابط عام للمشاركة وصفحة طباعة/تصدير PDF
- **المفضلة والتحليلات ومراجعات المستخدمين**
- **لوحة إدارة** — نظرة عامة، أماكن، فعاليات، مراجعات، وطلبات التخطيط
- **PWA** — قابل للتثبيت مع وضع دون إنترنت (تنقل NetworkFirst + تخزين بلاطات الخرائط والخطوط)
- **روابط حجز رسمية** للأماكن التي تبيع تذاكرها عبر الإنترنت

---

## التقنيات

| الطبقة            | التقنية                                             |
| ----------------- | --------------------------------------------------- |
| الخادم            | Laravel 13 + PHP 8.4                                 |
| الواجهة           | Inertia v3 + Svelte 5 (runes) + TailwindCSS 4        |
| الـAI             | `laravel/ai` مع Google Gemini (نص + صور + STT + TTS) |
| الخرائط           | Leaflet + OpenStreetMap                              |
| الطقس والمواقيت   | Open-Meteo + Aladhan                                 |
| قاعدة البيانات    | SQLite محلياً، PostgreSQL (Neon Serverless) إنتاجاً   |
| المصادقة والصلاحيات | Laravel Fortify + Passkeys + `spatie/laravel-permission` |
| PWA               | Vite PWA (استراتيجية injectManifest)                 |
| الأدوات           | Wayfinder (روابط TypeScript)، Pint، PHPStan، PHPUnit  |

---

## التشغيل المحلي

المتطلبات: PHP 8.4+، Composer، Node 22+.

```bash
# تثبيت كامل: الحزم + .env + المفتاح + الترحيل + بناء الواجهة
composer setup

# بعدها أضف مفتاح Gemini في .env
# GEMINI_API_KEY=...

# زرع بيانات المدن والأماكن والفعاليات والمراجعات
php artisan migrate --seed

# تشغيل الخادم + Vite معاً
composer run dev
```

أهم متغيرات البيئة:

```env
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
AI_PROVIDER=gemini
GEMINI_API_KEY=
ASSISTANT_TIMEZONE=Asia/Riyadh
```

---

## الجودة والاختبارات

```bash
php artisan test              # 206 اختبار (PHPUnit)
composer test                 # Pint + PHPStan + الاختبارات
composer types:check          # PHPStan
npm run check                 # تنسيق + lint للواجهة
npm run types:check           # svelte-check
npm run build                 # بناء الإنتاج + توليد روابط Wayfinder + service worker
```

---

## بنية المشروع (أهم المجلدات)

```
app/Ai/Agents/TouristGuide.php   وكيل المرشد السياحي وتوجيهاته
app/Ai/Tools/                    أدوات الوكيل (بحث، توصيات، مقارنة، ميزانية، طقس، اتجاهات، فعاليات...)
app/Http/Controllers/            المتحكمات (مساعد، رحلات، أماكن، إشعارات، إدارة...)
app/Support/                     أدوات مساعدة (مثل TripPayload)
resources/js/pages/              صفحات Inertia/Svelte
resources/js/components/         المكوّنات (الهيدر، بطاقات الأماكن، الخرائط...)
resources/js/lib/i18n/           الترجمات لعشر لغات
resources/pwa/sw.ts              Service worker (أوفلاين + تخزين مؤقت)
database/seeders/                بيانات المدن والأماكن والفعاليات والمراجعات
tests/Feature/                   اختبارات المزايا والمسارات
```

---

## اللغات المدعومة

العربية (افتراضي، RTL)، الإنجليزية، الفرنسية، الإسبانية، الألمانية، الروسية، التركية، الصينية، الهندية، الأردية.

---

## النشر (Laravel Cloud)

- المنطقة: `eu-central-1` (فرانكفورت)، قاعدة البيانات: Neon Serverless PostgreSQL.
- أمر البناء: `composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader` ثم `npm ci --audit false` ثم `npm run build`.
- أمر النشر: `php artisan migrate --force`.
- متغيرات البيئة: `APP_NAME="next trip"`، `APP_LOCALE=ar`، `APP_FALLBACK_LOCALE=en`، `AI_PROVIDER=gemini`، `ASSISTANT_TIMEZONE=Asia/Riyadh`، مع سر `GEMINI_API_KEY`. `APP_KEY` يولّده Cloud تلقائياً.
- تفعيل المجدول على الـinstance لتشغيل `php artisan schedule:run` (إشعارات 7:00 ص بتوقيت الرياض).
- زرع البيانات عند أول نشر (بالترتيب): `RolesSeeder` ثم `AdminUserSeeder` ثم `CitySeeder` ثم `PlaceSeeder` ثم `ReviewSeeder` ثم `EventSeeder`.
- غيّر كلمة مرور حساب الأدمن بعد أول تشغيل.

---

## حقوق الصور

صور المدن والأماكن والفعاليات مستخدمة من Wikimedia Commons بتراخيص حرة — التفاصيل في `public/images/cities/CREDITS.md` و`public/images/hero/CREDITS.md`.

---

## ملاحظة

مشروع تخرّج — Laravel 13 + Inertia + Svelte 5.
