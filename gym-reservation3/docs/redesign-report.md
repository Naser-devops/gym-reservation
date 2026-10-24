> **ملاحظة تاريخية:** هذا تقرير إعادة التصميم الأصلي بتاريخ 23 أكتوبر 2026، وليس وصفًا للحالة النهائية. في نسخة 24 أكتوبر تم إضافة الصورتين `hero.png` و`auth.png`، وتغيير الصفحة الافتراضية إلى Home، وتحديث بعض مكونات التشغيل والحماية. راجع `README.md` و`docs/changes-ar.md` للحالة الحالية.

# Frontend redesign report — 23 Oct 2026

## What was done
A premium dark/“volt” redesign of every page, built **only** in the view + asset layer of the existing PHP/PDO/MySQL project.
Models, Controllers, Core, Middleware, config, database, scripts, `.htaccess`, `router.php` are **byte-identical** to the uploaded ZIP.

### The one change outside views/assets (additive, 3 lines in `index.php`)
```php
} elseif ($page==='home') {
    getOnly(); renderView('home/index');
```
The original app had no landing page (default route is `register`). `?page=home` is new; **the default route is unchanged**.
To make the landing page the default, change `?? 'register'` to `?? 'home'` in `index.php` (your choice; not done to avoid altering existing behaviour).

## Files
**Modified:** `index.php` (+3 lines), `public/assets/css/style.css`, `public/assets/js/main.js`, and views: `layouts/header.php`, `layouts/footer.php`,
`home/index.php` (was an empty placeholder), `auth/login.php`, `auth/register.php`, `account.php`, `classes/index.php`, `reservations/index.php`, `errors/message.php`,
`admin/dashboard.php`, `admin/classes/{index,create,edit}.php`, `admin/reservations/index.php`, `admin/users/index.php`.
**Created:** `public/assets/css/components.css`, `public/assets/css/layout.css`, `public/assets/js/scene.js` (Three.js), `public/assets/js/dashboard.js` (Chart.js), `public/assets/images/README.md`, `docs/redesign-report.md`.
**Untouched:** everything else (all of `app/`, `config/`, `database/`, `scripts/`, `tests/`, `routes/`, `router.php`, `.htaccess`, `.env.example`, `README.md`, other docs, placeholder views `admin/login`, `classes/show`, `reservations/create|show`).

## Backend contract preserved (checked by script against the original views)
All `name=` attributes, hidden inputs (`id`, `status`, `is_active`, `time_slot_id`, `page`), form `method`/`action` targets (`linkTo(...)` pages), `csrfField()` in every POST form, `e()` escaping,
`isFuture()` status-action logic, filter forms (`date`, `search`, `status`) and flash handling (`$_SESSION['flash']` is read and unset exactly as before) are unchanged.
Reservation/user/class status changes still only happen via the existing POST endpoints. JavaScript never changes data; confirm dialogs only ask before submitting the same form.
Admin-class **Delete** deliberately keeps its original native `confirm()` so the guard works even without JavaScript.

## Design / tech
- Design tokens in `style.css`; components in `components.css`; shells and pages in `layout.css`. Three shells chosen in `header.php`: **site**, **auth** (split screen), **admin** (sidebar).
- Three.js r149 (UMD, pinned) metallic torus-knot + rings + particles scene on home and login/register: one renderer, paused off-screen/hidden tab, pixel-ratio capped, reduced on small screens, disposed on `pagehide`, static CSS fallback if WebGL/Three fails, honours `prefers-reduced-motion`.
- GSAP 3.12.5 + ScrollTrigger (pinned): hero entrance, reveals, staggered lists, animated counters. Everything works without them.
- Chart.js 4.4.8 (same version as before) doughnut on the admin dashboard, fed by the same real `$stats` values; the numeric legend remains if the CDN is offline.
- Responsive: tables become labelled cards below 760px; mobile nav + admin off-canvas sidebar.

## Test report
**Executed (in this environment):**
- `node --check` on `main.js`, `scene.js`, `dashboard.js` — PASS.
- Static audit script over all views vs the original: input names, hidden inputs, link targets, `csrfField()` in every POST form, bracket balance, if/endif and foreach/endforeach pairing — PASS (one script false-positive on nested parentheses in `reservations/index.php` was verified by hand: 5 `if`, 5 `endif`).
- Headless Chromium (offline) of 7 representative pages rendered from the real header/footer/views (home, login, register, admin dashboard) or from markup-identical fixtures (class cards, my reservations, admin reservations) at 1440px and 390px: **no horizontal overflow, no JS errors** after fixes.
- Diff against the original: backend files identical.

**NOT executed — please run on your XAMPP before relying on it:**
- `php -l` on any file (**PHP is not installed in the build environment**). The PHP in the views was reviewed by hand and by the static audit only.
- Any real PHP/MySQL flow: register, login, logout, booking, duplicate/capacity rules, cancel, admin approve/reject, user (de)activation, class create/edit/toggle/delete, statistics.
- Three.js scene rendering, GSAP motion and Chart.js (no internet: libraries could not be loaded). Syntax is valid but the visual result is unverified.
- Real-device / real-font appearance (Barlow Condensed + Inter load from Google Fonts; fallbacks were what I saw).
- Accessibility audit with a screen reader; Lighthouse performance.

## Known limitations
- **No photography**: no internet, so no images were downloaded. See `public/assets/images/README.md` to add licensed photos (CSS variables already wired).
- Libraries load from jsDelivr / Google Fonts at runtime (pinned versions, no SRI hashes). Self-host them for offline/production if needed.
- “My account” does not list upcoming sessions or stats because `account` receives no such data from the controller (showing them would require a controller change, which was out of scope).
- No invented data anywhere: landing-page copy only describes features the backend really has.

## Setup
Unchanged: `php -S localhost:8000 router.php`, open `http://localhost:8000/index.php?page=home` (or `?page=register`). Needs internet for CDN scripts/fonts the first time.
