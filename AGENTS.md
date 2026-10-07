# فكرة المشروع

**مرشد سياحي ذكي** — تطبيق ويب مدعوم بالذكاء الاصطناعي يساعد السياح في المملكة العربية السعودية: يخطط الرحلات، يقترح الأماكن والمطاعم، يعرض الخرائط والاتجاهات، يترجم فورياً، يتعرّف على المعالم من الصور، ويوفّر معلومات الطقس والأسعار.

# ملخص المتطلبات

| القرار                                             | البند             |
| -------------------------------------------------- | ----------------- |
| مرشد سياحي ذكي للسياحة في السعودية                 | الهدف             |
| متصفح (Web app)                                    | المنصة            |
| زائر + حساب اختياري (Email + Password)             | توثيق الدخول      |
| متعدد اللغات (عربي + إنجليزي + لغات أخرى، RTL/LTR) | اللغة             |
| أشهر المدن والمعالم السعودية                       | نطاق التغطية      |
| SAR (ريال سعودي)                                   | العملة            |
| OpenStreetMap + Leaflet                            | الخرائط والبيانات |
| سحابي (Cloud Sync)                                 | تخزين البيانات    |
| مساعد دردشة وكيل (Agentic AI Chat) + صوتي          | AI                |
| في شهر واحد MVP                                    | المدة             |

---

# متطلبات MVP (Must Have)

1. **الوصول** — زائر بدون حساب + حساب اختياري (Email + Password)
2. **مساعد ذكي محادث** — يخطط الرحلات ويجيب عن أسئلة السائح
3. **مساعد صوتي متعدد اللغات** — صوت ↔ نص، ويغيّر لغته مثل مترجم جوجل
4. **أماكن سياحية** — استعراض أشهر المعالم والمدن السعودية (OpenStreetMap)
5. **توصيات ومقارنة** — اقتراح ومقارنة الأماكن والمطاعم حسب الاهتمامات
6. **خرائط واتجاهات** — خريطة Leaflet/OpenStreetMap وحساب المسافات
7. **ترجمة فورية** — ترجمة النصوص والمحادثات بعدة لغات
8. **التعرّف على المعالم بالصور** — رفع صورة واستخراج معلومات المعلم
9. **الطقس والأسعار** — معلومات الطقس وأسعار التذاكر/الدخول
10. **متعدد اللغات** — واجهة بعدة لغات (RTL/LTR)

---

# MoSCoW Prioritization

## Must Have (MVP)

- Guest access + optional account (Email + Password)
- AI chat assistant (trip planning, Q&A, recommendations, comparison)
- Voice assistant with language switching (STT + TTS)
- Saudi tourist places catalog (cities + landmarks) — OpenStreetMap
- Maps & directions (Leaflet + OpenStreetMap)
- Multi-language UI (Arabic default + English + more, RTL/LTR)
- Instant translation
- Landmark recognition from images
- Weather & ticket prices
- Smart notifications (weather, events, upcoming trip)

## Should Have

- المفضلة (Favorites)
- مخطط رحلة يوم بيوم قابل للتعديل
- سجل المحادثات
- تصدير خطة الرحلة (PDF)
- مشاركة الخطة برابط
- تحليلات للمستخدم
- وضع بدون إنترنت (PWA / Offline cache)
- لوحة إدارة للمحتوى (Admin)

## Could Have

- حجز مباشر (تكامل خارجي)
- تقييمات ومراجعات المستخدمين
- أوقات العمل والازدحام المتوقعة
- تطبيق قابل للتركيب (PWA installable)

## Won't Have (v1)

- الدفع داخل التطبيق
- مشاركة اجتماعية بين المستخدمين
- تطبيق جوال أصلي (Native)

---

# AI Agent Capabilities (MVP)

1. **Trip Planning** — "خطط لي رحلة ٣ أيام في الرياض" (حسب المدينة + الميزانية + عدد الأشخاص + عدد الأيام)
2. **Place Q&A** — "وش أشهر الأماكن السياحية في العلا؟"
3. **Recommendations** — "اقترح مطاعم قريبة من الفيصلية"
4. **Place Comparison** — "قارن بين العلا وأبها" أو "قارن بين هذين المتحفين"
5. **Best-for-X** — "أفضل مكان للقهوة المختصة في الرياض؟"
6. **Budget Estimate** — تقدير التكلفة (تذاكر + مواصلات + وجبات + سكن) وتنبيه عند تجاوز الميزانية
7. **Directions** — "كم المسافة من جدة إلى الطائف؟"
8. **Translation** — "ترجم لي: أين محطة المترو؟"
9. **Landmark Recognition** — رفع صورة معلم واستخراج اسمه ومعلوماته
10. **Weather & Prices** — "وش الطقس في أبها؟ وكم تذكرة الدخول؟"
11. **Voice** — محادثة صوتية (STT/TTS) مع تغيير اللغة مثل مترجم جوجل
12. **Multi-language** — يفهم ويجاوب بأي لغة يدعمها التطبيق (تلقائي حسب لغة المستخدم)

# اختيار التقنيات

- Laravel
- Inertia (Laravel Svelte starter kit) + Svelte 5
- TailwindCSS
- laravel/ai (وكيل + أدوات)
- **AI:** Google Gemini عبر `laravel/ai` (نص + صور + تفريغ صوتي STT + نطق TTS) — يدعم لغات كثيرة
- **الخرائط:** Leaflet + OpenStreetMap
- **بيانات الأماكن:** OpenStreetMap / Overpass API
- **الطقس:** Open-Meteo (مجاني، بدون مفتاح)
- **الترجمة:** موديل الـAI (أو DeepL/Google لاحقاً)
- **PWA/Offline:** Vite PWA

# Design system

Mobile first
read DESIGN.md file (نظام تصميم Apple — واجهة عربية RTL بخط Inter للاتيني و IBM Plex Sans Arabic للعربي، أقرب بديل مفتوح لخطي SF Pro و SF Arabic)

# اللغات

- الواجهة متعددة اللغات (RTL/LTR) عبر ملفات `lang/{locale}` ومشاركة اللغة الحالية مع Inertia.
- العربية الافتراضية + الإنجليزية + لغات إضافية (مثال: الصينية، الفرنسية، الإسبانية، الروسية، التركية، الأردية، الهندية...).
- تبديل اللغة من الهيدر/الإعدادات، وحفظ التفضيل في `users.locale` (أو كوكيز للزائر).
- المساعد الصوتي يتعامل مع لغات أكثر من الواجهة (يترجم ديناميكياً).
- اتجاه الصفحة (`dir`) يتغير حسب اللغة (ar/he/fa = RTL).

# Frontend Pages

> الحالة: Svelte 5 (runes) داخل `resources/js/pages/`. الواجهة عربية RTL افتراضياً + 9 لغات إضافية.
> الهوية: الاسم المختصر **next trip** والعنوان الكامل «رحلتك القادمة وجميع فعالياتك في تطبيق واحد» (`app.name` / `app.nameAr`).

## الصفحات

| الصفحة                      | المسار       | الوصف                                                          |
| --------------------------- | ------------ | -------------------------------------------------------------- |
| Welcome / الترحيب           | `/`          | صفحة هبوط (هيرو + مميزات + نموذج طلب تخطيط)                    |
| Dashboard / الرئيسية        | `/dashboard` | ملخص: الطقس، رحلتك القادمة، تحليلات، توصيات                    |
| Assistant / المساعد         | `/assistant` | مساعد دردشة وكيل: تخطيط، أسئلة، توصيات، ترجمة، تعرّف على الصور |
| Places / الأماكن            | `/places`    | أشهر الأماكن والمعالم السعودية مع بحث/فلترة + خريطة            |
| Trip Planner / مخطط الرحلات | `/trips`     | بناء رحلة يوم بيوم + تصدير PDF + مشاركة برابط                  |
| Translate / الترجمة         | `/translate` | ترجمة فورية                                                    |

صفحات إضافية: `Favorites`, `Analytics`, `SharedTrip` (`/shared/{token}`), `TripPrint`, إعدادات الحساب، ولوحة الإدارة (`/admin`).

## ملاحظات التنفيذ

- التنقل العام: `SiteHeader` (هيدر ثابت، شفاف أعلى الصفحات ذات الهيرو الغامق ويتحول داكن عند السكرول) + `BottomNav` للجوال (مخفي في الصفحة الترحيبية)، وSidebar في صفحات الإعدادات/الإدارة.
- الروابط تُولَّد عبر Wayfinder في `resources/js/routes/`.

# مميزات إضافية

## تجربة التخطيط

- تقدير الميزانية تلقائياً (تذاكر + مواصلات + وجبات + سكن) وتنبيه عند التجاوز
- توزيع جغرافي ذكي للأيام لتقليل التنقل
- مراعاة الطقس (أنشطة داخلية/خارجية) وأفضل وقت للزيارة
- مراعاة أوقات الصلاة وإغلاق الجمعة وساعات رمضان
- بدائل تلقائية عند الإغلاق أو الازدحام

## الذكاء والمقارنة

- مقارنة جدولية بين الأماكن (السعر، التقييم، المسافة، المدة، مناسب للعائلة، أفضل وقت)
- "أفضل مكان فيه X" حسب الاهتمام
- ملخص تقييمات المراجعات
- توقع الزحام وأفضل وقت للزيارة

## التنقل والمعلومات العملية

- مسافات ووسائل النقل (قطار الحرمين، طيران داخلي، سيارة)
- معلومات السائح (تأشيرة، شرائح اتصال، صرف العملة، إتيكيت ثقافي، عبارات عربية)
- معلومات الطوارئ (أقرب مستشفى، شرطة سياحية)

## التخصيص والحفظ

- تفضيلات المستخدم (الميزانية الافتراضية، نوع السفر) وتُستخدم تلقائياً
- المفضلة وحفظ الرحلات، وتصدير/مشاركة الخطة (PDF/رابط)
- رحلة مقترحة جاهزة للمستخدم الجديد

## مناسبات وموسمية

- مواسم وفعاليات (موسم الرياض، جدة، العلا) وتنبيهات الفعاليات القريبة
- عروض وأسعار موسمية

## إمكانية الوصول

- فلترة للعائلات وكبار السن وذوي الإعاقة (كراسي متحركة، مصاعد)
- نقاط تصوير (أفضل مواقع وأوقات)

## الصوت واللغات

- مساعد صوتي (STT + TTS) مع تغيير اللغة مثل مترجم جوجل
- نطق أسماء الأماكن والعبارات بلغات متعددة

## الإشعارات

- تنبيهات الطقس والفعاليات ورحلتك القادمة

## وضع بدون إنترنت

- PWA (قابل للتثبيت) + تنقل NetworkFirst مع cache للصفحات المزارة + الرجوع إلى `offline.html` عند انقطاع الاتصال، وتخزين مؤقت لبلاطات OpenStreetMap وخطوط Google

## لوحة الإدارة

- إدارة الأماكن والفعاليات والمراجعات

## تحليلات المستخدم

- عدد الرحلات، الأماكن المزارة، الاهتمامات

# مخطط قاعدة البيانات

**`cities`** — `id, name, name_en, region, latitude, longitude, description, image`

**`places`** — `id, city_id, name, name_en, category, description, description_en, latitude, longitude, image, ticket_price, rating, opening_hours, tags, best_time, avg_visit_duration, is_indoor, family_friendly, wheelchair_accessible, prayer_facilities, closed_friday`
_category:_ `landmark, museum, heritage, restaurant, park, nature, beach, shopping`

**`trips`** — `id, user_id, title, city_id, start_date, end_date, travelers_count, budget, interests, notes, share_token, is_public`

**`trip_days`** — `id, trip_id, day_number, date, title, notes`

**`trip_items`** — `id, trip_day_id, place_id, title, type, start_time, duration_minutes, notes, sort_order`
_type:_ `activity, meal, transport, stay, note`

**`favorites`** — `id, user_id, place_id` (فريد: user+place)

**`conversations`** — `id, user_id, title`

**`messages`** — `id, conversation_id, role, content, tool_calls, audio_url, language`
_role:_ `user, assistant, tool`

**`events`** — `id, city_id, name, description, start_date, end_date, image, url`

**`reviews`** — `id, place_id, author, rating, content, source, reviewed_at`

**`user_preferences`** — `id, user_id, locale, default_budget, travel_style, interests`

**`notifications`** — `id, user_id, type, title, body, data, read_at`

**`users`** (إضافة) — `locale`

**العلاقات:** City→Places/Events، Trip→Days→Items→Place، User→Trips/Favorites/Conversations/Preferences/Notifications، Conversation→Messages، Place→Reviews.

# خارطة الطريق (خطوة بخطوة)

- [x] **0. التهيئة** — `composer install` + `npm install` + `.env` + `key:generate` + `migrate` ✅
- [x] **1. قاعدة البيانات** — Models + Migrations: `City`, `Place`, `Trip`, `TripDay`, `TripItem`, `Favorite`, `Conversation`, `Message`, `Event`, `Review`, `UserPreference`, `Notification` (+ `users.locale`) ✅
- [x] **2. البيانات الأولية** — Seeder لأشهر المدن والمعالم السعودية (OpenStreetMap) ✅ (9 مدن + 28 مكان)
- [x] **3. اللغات** — ملفات `lang/{locale}` + تبديل اللغة + RTL/LTR ✅ (10 لغات في الواجهة، `SetLocale` + كوكي + `users.locale` + مشاركة Inertia)
- [x] **4. الواجهات** — Dashboard, Places, Trip Planner, Translate, Assistant ✅ (بيانات حقيقية عبر Inertia shared props: `cities` + `places` — لا mock)
- [x] **5. الـAI** — وكيل `TouristGuide` (Gemini) + أدوات: `SearchPlaces`, `RecommendPlaces`, `ComparePlaces`, `FindBestFor`, `EstimateBudget`, `GetWeather`, `GetDirections`, `BuildItinerary`, `ListEvents` + `Translator` + محادثات محفوظة (SDK `RemembersConversations`) + تعرّف المعالم بالصور عبر مرفقات Gemini ✅
- [x] **6. الصوت** — STT (Gemini transcribe) + TTS (Gemini audio) + تبديل اللغة مع fallback لمتصفح (SpeechSynthesis) ✅
- [x] **7. التكاملات الخارجية** — Leaflet/OpenStreetMap (خرائط Places + MiniMap)، طقس Open-Meteo، مواقيت الصلاة (Aladhan)، الأسعار من قاعدة البيانات ✅
- [x] **8. الإشعارات** — `notifications:dispatch` مجدول 7ص بتوقيت الرياض (تذكير رحلة + تنبيه طقس/مدن متعددة + فعاليات قريبة) + جرس الإشعارات في الهيدر ✅
- [x] **9. الاختبارات** — Feature tests شاملة للمسارات والوكيل ✅ (190 اختبار / 994 assertion — كلها ناجحة؛ تغطي الصفحات، حماية الأدمن، passkey endpoints، 404 للطقس/المواقيت، pagination المراجعات، ملكية محادثات المساعد، بيانات الـSeeders، وأدوات الوكيل)
- [x] **10. التحسينات** — ✅ مشاركة الرابط (رابط عام `/shared/{token}` + صفحة `SharedTrip` + واجهة في مخطط الرحلات + 8 اختبارات) ✅ المفضلة (اختبارات + روابط في الهيدر والـSidebar) ✅ تصدير PDF (صفحة طباعة `TripPrint` حسب اللغة + زر في المخطط) ✅ تحليلات (صفحة `/analytics` + JSON endpoint) ✅ لوحة إدارة (`spatie/laravel-permission` — نظرة عامة/أماكن/فعاليات/مراجعات/طلبات + دور admin عبر `AdminUserSeeder` بـadmin@example.com) ✅ PWA offline (استراتيجية `injectManifest` + `PrecacheFallbackPlugin`: تنقل NetworkFirst مع cache للصفحات + الرجوع إلى `offline.html`، وتخزين بلاطات OSM وخطوط Google) ✅ نموذج طلب التخطيط (`contact_requests` + صفحة إدارة) ✅ مراجعات المستخدمين (تقييم بالنجوم + عرض المراجعات) ✅ الازدحام المتوقع وأفضل وقت للزيارة ✅ زر تثبيت PWA ✅ محتوى أغنى (صور للمدن والأماكن والفعاليات + `ReviewSeeder` بـ2–4 مراجعات لكل مكان) ✅ هوية «next trip» (الواجهة/المانيفست/صفحة الأوفلاين) ✅ هيدر شفاف أعلى الهيرو ✅ إخفاء الشريط السفلي في الصفحة الترحيبية ✅ إصلاح لغة نطق الصوت + رد JSON لتبديل اللغة ✅ **سجل المحادثات** (عرض/تبديل/بدء محادثة جديدة/حذف — نقاط JSON محمية بالملكية) ✅ **مخطط رحلة قابل للتعديل** (إضافة/حذف/ترتيب/تعديل وقت العناصر) ✅ **روابط حجز رسمية** (`places.booking_url` + زر «احجز الآن» + حقل في لوحة الإدارة) ✅ README
- [x] **11. النشر** — Laravel Cloud (منطقة `eu-central-1`، Neon Serverless PostgreSQL، سر `GEMINI_API_KEY`، مجدول مفعّل) — الرابط: <https://next-trip-production-wbbwxq.laravel.cloud> — 206 اختبار / 1059 assertion

# Instructions

Before doing anything related to framework or tool ensure you have enough information about it read the docs using the context7 mcp server

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.4. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:

- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
    - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-svelte-development` when working with Inertia Svelte client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-svelte/core rules ===

# Inertia + Svelte

- IMPORTANT: Activate `inertia-svelte-development` when working with Inertia Svelte client-side patterns.

</laravel-boost-guidelines>
