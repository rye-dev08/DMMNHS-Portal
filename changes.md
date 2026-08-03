# Change Log

## DMMNHS-V.1: Project Inspection & Documentation

**Date:** 2026-08-03

### Files Changed
- `memory.md` (created)
- `changes.md` (created)

### Changes
- Performed a full inspection of the existing legacy PHP project located in `dmmnhs/`.
- Documented the current project state, legacy architecture, functionality, database schema, roles, frontend structure, and migration considerations in `memory.md`.
- Created this change log with the initial project version `DMMNHS-V.1`.

### Reason
To establish the baseline documentation required before starting the Laravel migration. The task was inspection and documentation only; **no application code was migrated or refactored.**

### Notes
- No Laravel/application code was changed.
- No Git operations were performed.
- The Laravel root app (`C:\laragon\www\student-portal\`) is a fresh Laravel 13 starter; the legacy app lives in `dmmnhs/`.

---

## DMMNHS-V.1.1: Laravel Migration — Foundation, Database, Auth

**Date:** 2026-08-03

### Files Changed
- `.env` — switched to MySQL (`student_portal` DB on Laragon), fixed `APP_NAME` quoting.
- `composer.json` — added `app/Support/helpers.php` to `autoload.files`.
- `database/migrations/` — created migrations for all legacy tables plus `teacher_subjects`.
- `database/seeders/` — `SettingSeeder` (settings singleton) and `DatabaseSeeder` (dev admin `admin`/`Admin123!`).
- `app/Models/` — User, Student, Teacher, TeacherApproval, EnrollmentRequest, Subject, Grade, AssessmentScore, Setting, PreviousSemesterSubject, PreviousSemesterGrade, GraduatedStudent, TeacherSubject.
- `app/Http/Controllers/Auth/AuthController.php`, `app/Http/Middleware/CheckRole.php`, `app/Rules/PasswordPolicy.php`, `app/Support/helpers.php`.
- `resources/views/components/{layouts,notice,card}`, `resources/views/auth/login.blade.php`.

### Changes
- Created local MySQL database `student_portal` and connected the Laravel app to it (replacing default sqlite).
- Migrated the entire legacy schema with identical table/column names; added unique indexes to match legacy constraints; seeded `settings` singleton (`current_semester=1`, `school_year='2025-2026'`) and a dev admin account.
- Implemented Laravel built-in auth wired to the legacy `users.password_hash` column via custom `getAuthPassword()` (keeps plaintext fallback + auto-rehash behavior).
- Added `role` middleware for `admin`/`teacher`/`student` gating; inactive users cannot log in.
- Ported password policy (min 8 chars + uppercase OR symbol) as a shared validation rule and a single change-password page.

### Reason
Required as the foundation of the full migration so all role modules could be built on the correct schema and auth layer.

### Verification
- `composer dump-autoload` succeeded after `.env` APP_NAME quoting fix.
- `php artisan migrate --seed --force` — all migrations applied, seeders ran (users=1, settings=1).
- `php artisan route:list` — all routes registered.
- `npm install` + `npx vite build` — production assets built into `public/build/`.

### Notes
- No Git operations were performed.
- Legacy `dmmnhs/` project left untouched.

---

## DMMNHS-V.1.2: Laravel Migration — Admin, Teacher, Student Modules

**Date:** 2026-08-03

### Files Changed
- `routes/web.php` — full public, auth, admin.*, teacher.*, student.* route groups.
- `app/Http/Controllers/Admin/` — Dashboard, Account (create/delete users + profiles), TeacherApproval (approve + limits + advisory), EnrollmentSetting (advisory, end semester, end school year).
- `app/Http/Controllers/Teacher/` — Dashboard, Subject (advisory portal add/delete + auto-apply), EnrollmentRequest (capacity check, approve auto-applies subjects), Grade (submit + JSON subjects endpoint), GradesOverview, Info.
- `app/Http/Controllers/Student/` — Dashboard, StudentInfo, Schedule, Grade (GWA + color coding), Enrollment (guards: graduate/inactive/duplicate, capacity).
- `app/Http/Controllers/PasswordController.php`, `app/Http/Controllers/PageController.php`.
- `resources/views/admin/`, `resources/views/teacher/`, `resources/views/student/` — Blade views for every legacy page.
- `resources/css/app.css`, `resources/js/app.js` — ported legacy styling (Tailwind) and JS behavior.
- `public/images/dmnhs-no-bg.jpg` — school logo copied into Laravel public dir.

### Changes
- Ported every legacy feature page into Blade + Laravel controllers, preserving exact role behavior, flash notices, grade mapping (N/A / INC / DROPPED / thresholds), capacity logic (`COALESCE(teacher_approval.max_students, teachers.max_students, 30)`), semester/school-year archive flows, and JSON subjects endpoint.
- Kept dead/broken legacy bits out of scope by design: `account.php`, `update_grade.php` reference, un-wired `contact.php` form.

### Reason
To complete the migration of the full student portal into Laravel.

### Verification (manual smoke tests via `php artisan serve`)
- Public pages `/`, `/about`, `/contact` → 200.
- Guest access to role routes → 302 redirect to login.
- Admin login → dashboard, accounts (create teacher+student users with profiles), approve teacher (activates + sets limits/advisory), enrollment-settings → 200.
- Teacher login → dashboard, advisory portal (adds `teacher_subjects`), enrollment-requests (approve auto-applies subjects), submit-grades (JSON endpoint + grade upsert `quarter='Sem 1'`), grades-overview, info, change-password → 200.
- Student login → dashboard, info, schedule, grades (shows grade `92` for Mathematics), enrollment → 200.
- Full data flow persisted: `teacher_subjects`(1) → `enrollment_requests`(approved) → `subjects`(auto-applied) → `grades`(92, Sem 1).

### Notes
- No Git operations were performed.
- Legacy `dmmnhs/` project left untouched.
- After verification, all smoke-test data (test users, subjects, enrollment, grades) was removed; DB left pristine with only the seeded `admin` user and `settings` singleton.

---

## DMMNHS-V.1.3: Demo Data, Contact Form Wiring, Credential Cleanup

**Date:** 2026-08-03

### Files Changed
- `database/seeders/DemoSeeder.php` (new) — realistic demo data (4 teachers, 11 students, subjects, approved enrollments, grades).
- `database/migrations/2026_08_03_000013_create_contact_messages_table.php` (new).
- `app/Models/ContactMessage.php` (new).
- `app/Http/Controllers/PageController.php` — added `submitContact()`.
- `routes/web.php` — added `POST /contact` (`contact.submit`).
- `resources/views/contact.blade.php` — wired the form to the backend (POST, CSRF, validation messages, subject field).
- `dmmnhs/includes/config.php` — removed live InfinityFree credentials; replaced with local placeholders.

### Changes
- **DemoSeeder** (`php artisan db:seed --class=Database\Seeders\DemoSeeder --force`): 4 teachers (advisory 7-A/B/C, 8-A) each with 5 subjects, 11 students with approved enrollment requests, auto-applied subjects, and Sem 1 grades (80–94, DepEd-style remarks). All demo accounts share password `Demo123!`.
- **Contact form** now stores messages in the new `contact_messages` table (name, email, subject, message) with server-side validation and a success flash notice.
- **Legacy credentials** neutralized in `dmmnhs/includes/config.php` (the live InfinityFree host/user/password removed and replaced with local `student_portal` placeholders).

### Verification
- Demo student `juan.dela.cruz` / `Demo123!` logs in and the Grades page renders all 5 subjects + GWA.
- Demo teacher `maria.santos` / `Demo123!` logs in and reaches the dashboard.
- Contact form POST stored a message (row present in `contact_messages`), then test row removed.

### Notes
- `contact_messages` migration applied to MySQL without conflict.
- No Git operations were performed.

---

## DMMNHS-V.1.4: Login Page Redesign + Code Manner

**Date:** 2026-08-03

### Files Changed
- `codemanner.md` (new) — the authoritative ruleset for this repo (working rules, code style, framework conventions, preservation rules, verification workflow, docs management).
- `resources/views/auth/login.blade.php` — refactored into a full-screen two-column design (42% branding panel / 58% login area) using reusable components.
- `resources/views/components/brand.blade.php` (new) — school logo + uppercase tagline.
- `resources/views/components/decorative-background.blade.php` (new) — subtle grid lines, glowing dots, faint lines, radial glows.
- `resources/views/components/login-card.blade.php` (new) — white login card with icon container, title/subtitle, slot.
- `resources/views/components/form-input.blade.php` (new) — reusable labeled input with right-side icon slot + `@error`.
- `resources/views/components/password-input.blade.php` (new) — password field with eye toggle button.
- `resources/views/components/primary-button.blade.php` (new) — full-width blue-gradient submit button.
- `resources/views/components/google-button.blade.php` (new) — white bordered Google sign-in button.
- `resources/views/components/layouts/guest.blade.php` — body no longer centers content (login owns its layout).
- `resources/js/app.js` — added `initPasswordToggles()` for `[data-password-toggle]` eye buttons.
- `public/build/` — rebuilt production assets.

### Changes
- Login page recreated from the provided UI spec: dark navy→blue gradient branding panel with subtle futuristic decoration (grid, glowing dots, thin lines, radial glows), school logo + "SHAPING MINDS. BUILDING FUTURES." tagline, "Welcome to DMMNHS Student Portal" gradient heading, accent rule, and copyright footer.
- Right side: centered white login card (rounded 16px, subtle shadow) with lock icon container, "Login to Your Account" heading, labeled inputs (Email or Student ID, Password) with icons, right-aligned Forgot Password link (→ contact), gradient "Sign In →" button, "or continue with" divider, Google button (shows an info notice since OAuth is not configured), and "Don't have an account? Contact your administrator." footer link.
- Branding uses the real school identity ("DMMNHS Student Portal"); the reference image's fictional "Nexora Academy" name was replaced.
- Responsive: 42/58 split on desktop, compact branding header + full-width card on mobile. A building photo will auto-appear at the bottom of the panel if placed at `public/images/campus.jpg`.
- Form contract unchanged: POSTs to `login.attempt` with `username`/`password` + CSRF; flash notices still render via `#alert-host` + `<x-notice />`.

### Verification
- `npx vite build` succeeded; built CSS contains all new Tailwind classes (42%/58% widths, 52px fields, 16px card radius, gradients).
- Removed stale `public/hot` (leftover from a running Vite dev server) so the app serves the production build.
- Smoke test: `/` 200 with new layout (tagline, welcome, card, eye toggle, Google button, forgot link); admin login → dashboard 200; demo student login → grades 200 showing Mathematics.

### Notes
- No Git operations were performed.
- Google sign-in is decorative (OAuth not configured) — clicking it shows an info notice.

---

## DMMNHS-V.1.5: Split Admin Accounts (Create / Manage) + Audit Fixes

**Date:** 2026-08-03

### Files Changed
- `app/Http/Controllers/Admin/AccountController.php` — rewritten: `index` (role filter tabs, search, paginate 15, student grade/section + teacher advisory), `create`, `store` (student active / teacher inactive, creates profile rows), `edit`, `update`, `toggleStatus`, `resetPassword`, `destroy` (transactional cascade + guards), `activeAdminCount`.
- `app/Http/Requests/CreateUserRequest.php` — role rule widened to `in:admin,teacher,student`.
- `app/Http/Requests/UpdateUserRequest.php` (new) — name/email + role-specific profile fields.
- `resources/views/admin/accounts.blade.php` — rewritten: header with "+ New Account", search + role tabs, table (name/username/email/role/grade-advisory/status/actions), Edit / Activate-Deactivate / Delete buttons, pagination links.
- `resources/views/admin/create_account.blade.php` (new) — separate create form with role selector, student info grid, password show/hide toggle, password-policy hint.
- `resources/views/admin/edit_account.blade.php` (new) — edit form (role-specific profile fields) + separate reset-password form.
- `routes/web.php` — added `accounts.create`, `accounts.edit`, `accounts.update`, `accounts.toggle-status`, `accounts.reset-password`.

### Changes
- Split the single admin account page into **Create Account** and **Manage Accounts** pages. Manage now has role-filter tabs (All/Students/Teachers/Admins), free-text search, 15-per-page pagination, inline activate/deactivate toggle, delete with cascade cleanup of all related rows, and a dedicated reset-password form.
- Added safety guards: cannot deactivate or delete your own account; cannot deactivate/delete the last active admin.
- `destroy` wraps cleanup + user deletion in `DB::transaction` and removes dependent rows (grades, subjects, enrollments, assessment scores, previous-semester data, teacher approvals, teacher_subjects, sessions) before deleting the user.

### Verification
- `npx vite build` succeeded.
- Smoke-tested via `php artisan serve`: admin login → `/admin/accounts` 200 (renders "Manage Accounts" + "New Account"); create page 200; edit page 200 for teacher (advisory fields) and student (grade-level fields) accounts; POST create created user + student profile; toggle-status flips status (302); self-toggle redirects back without change; DELETE removed user + no orphan student rows; admin stayed active after self-toggle attempt.

### Notes
- No Git operations were performed.
- The subject-logic and authorization/security fixes identified in the audit are still pending and tracked in `memory.md`.

---

## DMMNHS-V.1.6: Unified Futuristic Sidebar + Header + Footer Layout

**Date:** 2026-08-03

### Files Changed
- `resources/views/components/layouts/sidebar.blade.php` (new) — role-aware futuristic sidebar navigation.
- `resources/views/components/layouts/app.blade.php` — rebuilt to compose sidebar + top header + content column + footer.
- `resources/views/components/layouts/header.blade.php` — rewritten as a slim futuristic top bar (mobile hamburger, branding, role chip, home, logout).
- `resources/views/components/layouts/footer.blade.php` — restyled futuristic footer matching the sidebar/header.
- `resources/js/app.js` — `initHeaderMenu()` now toggles the mobile sidebar drawer (was a dropdown panel).

### Changes
- All roles (admin/teacher/student) now inherit a **single shared layout** via `<x-layouts.app />`: a persistent **sidebar** on the left, a slim **top header**, and a matching **footer** — no more per-role navigation markup.
- Sidebar is **futuristic** and consistent with the login page: dark navy→blue gradient, decorative grid + glowing dots (reuses `x-decorative-background`), glowing logo, gradient profile avatar chip, grouped navigation with active-link glow, and a logout row. The logo/branding and nav items are derived automatically from the user's role.
- Responsive: sidebar is fixed on `lg+`, and becomes an off-canvas drawer toggled by the hamburger (with a blurred overlay backdrop) on smaller screens.
- Header retains the hamburger trigger, school branding, role badge, a home button, and logout; footer shows the copyright line on the same gradient.

### Verification
- `npx vite build` succeeded (CSS grew to ~80 kB with the new sidebar/gradient utility classes).
- Smoke-tested via `php artisan serve`: admin dashboard 200 (sidebar + menu-toggle + logout-form + footer present); student dashboard 200 with student menu items; teacher dashboard 200 with teacher menu items.

### Notes
- No Git operations were performed.
- `decorative-background` is reused both on the login page and the sidebar for a consistent look.

---

## DMMNHS-V.1.7: Futuristic Content Panels & Admin Pages

**Date:** 2026-08-03

### Files Changed
- `resources/views/components/layouts/app.blade.php` — main content `<main>` upgraded into a detailed futuristic panel.
- `resources/views/admin/accounts.blade.php` — futuristic gradient header bar, tab pills, search, striped table with gradient header, role badges.
- `resources/views/admin/create_account.blade.php` — rebuilt into a structured numbered-section form (Credentials / Role radio cards / Student profile) on a futuristic card.
- `resources/views/admin/edit_account.blade.php` — same card style for account/profile editing plus a dedicated reset-password card with warning accent.
- `resources/views/admin/approve_teachers.blade.php` — futuristic table + empty state.
- `resources/views/admin/enrollment_settings.blade.php` — current-period card, gradient action buttons, futuristic advisory table.

### Changes
- The shared content panel (the former plain yellow box) is now a layered futuristic surface shared by every page: gradient border ring with outer glow, grid-line backdrop, corner brackets, a blue gradient top status line, soft radial glows, and a light glass background (keeps content readable).
- All admin pages restyled to match: dark navy gradient table headers with white text, zebra rows with hover tint, blue-accent inputs with focus rings, gradient buttons with glow shadows, page headers with an accent bar, and structured step-numbered form sections on the account forms.

### Verification
- `npx vite build` succeeded (CSS grew to ~95 kB with the new utilities).
- Smoke-tested via `php artisan serve` (admin + demo teacher + demo student logins): all admin pages (accounts, create, edit, approve-teachers, enrollment-settings) and all teacher/student pages returned HTTP 200.

### Notes
- No Git operations were performed.

---

---

## DMMNHS-V.1.8: Removed Remaining Yellow Boxes

**Date:** 2026-08-03

### Files Changed
- `resources/views/components/card.blade.php` — restyled from the yellow box to a futuristic glass card (blue gradient top line + corner bracket accents).
- `resources/views/student/student_info.blade.php` — info section restyled (was yellow box) with gradient header bar + white field tiles.
- `resources/views/teacher/info.blade.php` — same restyle as student info.
- `resources/views/components/layouts/app.blade.php` — main panel border/gradient switched from yellow (`#e6c84b`) to blue/cyan.

### Changes
- Eliminated every remaining yellow-tinted surface (`#e6c84b`, `rgba(255,246,179,…)`, `#facc15`, `#9a3412`) across the shared card component, the student/teacher info sections, and the main panel, replacing them with the same blue/cyan futuristic treatment used elsewhere.
- All pages that used `<x-card>` (student/teacher dashboards, about, contact) now render the new futuristic card automatically.

### Verification
- `npx vite build` succeeded.
- Smoke-tested via `php artisan serve`: student dashboard/info, teacher dashboard/info, admin accounts all HTTP 200.

### Notes
- No Git operations were performed.

---

## DMMNHS-V.1.9: UI Polish & Emoji Removal

**Date:** 2026-08-03

### Files Changed
- `app/Http/Controllers/Admin/DashboardController.php` — now passes role/pending stats to the dashboard.
- `resources/views/admin/dashboard.blade.php` — rebuilt: gradient hero header, stat cards (students/teachers/admins/pending teachers/pending enrollments), quick-action cards with hover lift.
- `resources/views/admin/enrollment_settings.blade.php` — emoji icons replaced with inline SVG icons; shadow/glow polish.
- `resources/views/teacher/dashboard.blade.php` — gradient hero, privacy notice as a subtle icon banner, stat cards.
- `resources/views/student/dashboard.blade.php` — gradient hero + icon privacy banner.
- `resources/views/teacher/{advisory_portal,enrollment_requests,submit_grades,grades_overview}.blade.php` — full futuristic restyle (removed legacy yellow empty states, inline-styled tables, emoji headings).
- `resources/views/student/{class_schedule,grades,enrollment_request}.blade.php` — same restyle (gradient tables, empty states, status badges, GWA card).
- `resources/views/password/change.blade.php`, `resources/views/about.blade.php`, `resources/views/contact.blade.php` — consistent futuristic cards/forms/nav pills.

### Changes
- Removed **all emojis** from the entire app (verified via regex across `resources/`); replaced the decorative ones with clean inline SVG icons. Remaining amber/red/emerald colors are functional status badges only (not decorative boxes).
- Standardized every page: blue-accent page headers with a gradient bar, dark-navy gradient table headers, zebra rows with hover tint, futuristic empty states/cards, gradient buttons with soft glow shadows, and `active:scale` micro-interactions.
- Admin dashboard shows live counts and quick-link cards.

### Verification
- `npx vite build` succeeded (CSS ~101 kB).
- Smoke-tested all pages (admin/teacher/student) via `php artisan serve` → all HTTP 200.

### Notes
- No Git operations were performed.

---

---

## DMMNHS-V.2.0: Global Notifications & Custom Form Validation

**Date:** 2026-08-04

### Files Changed
- `resources/js/app.js` — rebuilt global notification system (polished sliding toasts with icons/titles/progress bars + centered success modal), added `showModal()`, generic `initFormValidation()` custom inline validation, kept backward-compat `showNotice()`/`floatingAlert()` aliases; `window.alert()` now maps to a styled toast.
- `resources/css/app.css` — added `.toast-host`, `.toast*` (variant bars, icons, progress animation), `.modal-overlay/.modal-card/.modal-icon` (success modal + variant glows), and `.field-error/.field-invalid/.field-shake` validation styles.
- `app/Support/helpers.php` — added `flash_modal()` helper (flashes a prominent centered modal, separate from toast `flash_notice`).
- `resources/views/components/notice.blade.php` — now renders both `flash_notice` (as toast) and `flash_modal` (as centered modal).
- `app/Http/Controllers/PasswordController.php` — password-change success now uses `flash_modal(...)` ("Password Updated" centered modal).
- `resources/views/auth/login.blade.php` — added `data-validate` + `novalidate` (custom inline validation instead of native browser bubbles); removed obsolete legacy `#alert-host` div.
- `resources/views/password/change.blade.php` — added `data-validate`, `data-match="new_password"` (confirm match), `data-password-policy`, `data-min="8"` for custom inline validation.
- `resources/views/components/layouts/app.blade.php` — removed obsolete legacy `#alert-host` div.

### Changes
- Replaced the old fragile `.fb-alert` top-of-page banners with a polished fixed notification system: top-right **toasts** (slide-in, per-type colored icons/accent bar/progress countdown, hover-pause, auto-dismiss, manual close) used for all `flash_notice()`/controller notices; and a centered **success modal** (glowing icon, title, message, gradient OK button) for major confirmations (e.g. "Password Updated").
- Built a generic **custom form validator** (`form[data-validate]` sets `novalidate`): fields validate on required / min-length / pattern / `data-match` (confirm password) / password-policy; invalid fields get a red border + animated shake + inline `.field-error` message, and the first invalid field is focused — replacing the native browser "this field is required" bubbles.
- Applied the validator to the login form and the change-password form.
- Success/error messages server-side are delivered as toasts via the existing `<x-notice />` wiring (verified login error flash → `showToast(...)`).

### Verification
- `npx vite build` succeeded (JS ~5.9 kB, CSS ~106 kB).
- `php artisan view:cache` succeeded.
- Smoke-tested via `php artisan serve`: `/` (login) → 200 with `data-validate` present; `/change-password` → 200; a bad POST to `/login` returned flash `showToast(...)` (toast wired end-to-end).

### Notes
- Backward compatible: `showNotice()`, `floatingAlert()`, and `window.alert()` still resolve to styled toasts, so existing inline calls keep working.
- No Git operations were performed.

---

