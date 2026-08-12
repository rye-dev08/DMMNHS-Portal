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
  - `resources/views/components/layouts/app.blade.php` — removed obsolete legacy `#alert-host` div. Added inline `<script>` in `<head>` to read `localStorage` and apply `sidebar-collapsed` class to `<html>` before first paint, preventing the "zoom/zoom-out" jump on page load. CSS and JS switched to `html.sidebar-collapsed` selector (from `body`) for consistency with the server-side class application.

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

## DMMNHS-V.2.1: Self-Service Info Editing (Student & Teacher)

**Date:** 2026-08-04

### Changes
- Students can now edit their own info, and teachers their own info, via the "My Info" section (no admin intervention required).

### Verification
- Smoke-tested student/teacher info editing via `php artisan serve` → 200.

### Notes
- No Git operations were performed.

---

## DMMNHS-V.2.2: Fix Grade/Subject Duplication (Per-Student Subjects)

### Changes
- Subjects are now created per-student to avoid duplicate grade entries; the subject duplicate check skips empty `course_code` values.

### Notes
- No Git operations were performed.

---

## DMMNHS-V.2.4: Audit Security & Correctness Fixes

**Date:** 2026-08-04

### Files Changed
- `app/Http/Controllers/Student/GradeController.php` — added teacher-ownership checks on grade operations; `getSubjects` returns 403 for non-owners.
- `app/Http/Controllers/Admin/EnrollmentSettingController.php` — `endSchoolYear()` now writes `graduated_students`.
- `app/Providers/AppServiceProvider.php` — named rate limiters `login` (5/min) and `contact` (3/min); wired via `throttle:` middleware on POST `/login` and `/contact`.
- `routes/web.php` — `/teacher/subjects` moved inside the `role:teacher` group.
- Subject duplicate check skips empty `course_code`; subject-delete grade-block keyed by `subject_name`; teacher dashboard null-guards; Grades overview term filter.

### Notes
- No Git operations were performed.

---

## DMMNHS-V.2.5: Teachers Auto-Approved on Creation (Remove Approve Step)

**Date:** 2026-08-04

### Files Changed
- `app/Http/Controllers/Admin/AccountController.php` — creating a teacher now sets `active` = 1 and writes an approved `teacher_approval` row.
- Removed `TeacherApprovalController`, `ApproveTeacherRequest`, `approve_teachers.blade.php`, the `admin/approve-teachers` routes, sidebar/dashboard approve links, and the `pendingTeachers` stat; cleaned `about.blade.php` references.
- `app/Http/Requests/CreateUserRequest.php` — teacher fields (`advisory_class`, `max_students`, `max_subjects`).
- `resources/views/admin/create_account.blade.php` — added Teacher Profile section.

### Changes
- Teacher accounts are auto-approved on creation — no separate Approve Teachers step. Approved status and advisory capacity come from the Create Account "Teacher Profile" form (blank → settings defaults). `TeacherApproval` model + `teacher_approval` table remain.

### Notes
- No Git operations were performed.

---

## DMMNHS-V.2.5.1: Fix Student Grades History (Archived Remarks)

**Date:** 2026-08-04

### Files Changed
- `database/migrations/2026_08_04_000004_add_remarks_to_previous_term_grades_table.php` (new) — adds nullable `remarks` to `previous_term_grades`.
- `app/Http/Controllers/Admin/EnrollmentSettingController.php` — `archiveGrades()` now copies `remarks` from `grades` into the archive.
- `app/Models/PreviousTermGrade.php` — added `remarks` to `fillable`.

### Changes
- The student grades page crashed with `Unknown column 'g.remarks'` on an archived term. The archive now has `remarks` and future archiving preserves it.

### Verification
- `php artisan migrate --force` succeeded; history query for demo student returns 5 rows with remarks; 55 archived rows backfilled (19 Outstanding, 32 Satisfactory, 4 Fairly Satisfactory) using the `DemoSeeder` deterministic remarks.

### Notes
- No Git operations were performed.

---

## DMMNHS-V.2.5.2: Collapsible Desktop Sidebar

**Date:** 2026-08-04

### Files Changed
- `resources/css/app.css` — added a `lg+` media-query block: when `body` carries `.sidebar-collapsed` the fixed sidebar translates off-canvas (`translate: -100% 0`) and the content shell loses its 264px left padding.
- `resources/views/components/layouts/app.blade.php` — gave the content wrapper the id `app-shell` and a `transition-[padding]` (300ms) so collapse/expand animates.
- `resources/views/components/layouts/header.blade.php` — added a desktop-only collapse toggle button (id `sidebar-collapse-toggle`, `hidden lg:inline-flex`).
- `resources/js/app.js` — added `initSidebarCollapse()`: toggles the `sidebar-collapsed` body class on click, persists to `localStorage` (`sidebar-collapsed` = `1`), and restores it on load.

### Changes
- The fixed 264px desktop sidebar can now be hidden via a header toggle so pages use the full available width. The state persists across reloads. Mobile drawer behavior is unchanged.

### Verification
- `npm run build` compiled cleanly; built CSS contains the `sidebar-collapsed` hide rules.
- `php artisan view:cache` compiled all Blade templates with no syntax errors.
- `php -l` clean (no PHP files changed).

### Notes
- Used a persisted body class + plain CSS (the `translate` property, matching Tailwind v4) instead of relying on utility-class ordering. No new packages. No Git operations were performed.

---

## DMMNHS-V.2.6: In-App Notification System (Portal + Email)

**Date:** 2026-08-05

### Files Changed
- `database/migrations/2026_08_05_000001_create_notifications_table.php` (new) — Laravel `notifications` table (uuid id, type, notifiable morph, data JSON, read_at, timestamps).
- `database/migrations/2026_08_05_000002_create_grade_completion_flags_table.php` (new) — per student/term/school-year dedup flags for the grades-complete notification.
- `app/Models/GradeCompletionFlag.php` (new).
- `app/Notifications/PortalNotification.php` (new) — portal-only notification (database channel).
- `app/Notifications/PortalMailNotification.php` (new) — portal + email notification; mail channel is only used when the recipient has an `email` on file.
- `app/Services/NotificationService.php` (new) — centralized sender: every event method decides recipient(s), message, link and whether an email is also sent.
- `app/Http/Controllers/NotificationController.php` (new) — `/notifications` index, open (mark read + redirect to target), read-all.
- `routes/web.php` — added `notifications.index`, `notifications.open`, `notifications.read-all` (all inside the `auth` group so every role can use them).
- `resources/views/components/notification-icon.blade.php` (new) — reusable kind-aware SVG icon.
- `resources/views/notifications/index.blade.php` (new) — full notification history page with pagination.
- `resources/views/components/layouts/header.blade.php` — notifications bell + unread badge + dropdown panel (latest 8, mark all read, view all).
- `resources/js/app.js` — `initNotificationsBell()` dropdown toggle / click-outside / Escape close.
- Wired controllers: `PasswordController` (self password change), `Admin/AccountController` (reset password, account update), `Admin/EnrollmentSettingController` (advisory assign/change, end term, end school year → phase opened, end phase, new school year), `Teacher/EnrollmentRequestController` (approve/reject), `Student/EnrollmentController` (request sent), `Teacher/GradeController` (grade submitted + grades-complete), `Teacher/SubjectController` (subject added/removed + completion resync), `Student/StudentInfoController` & `Teacher/InfoController` (profile updated).
- `tests/Feature/NotificationTest.php` (new) — 5 tests covering grade-submitted, grades-complete dedup + refire, enrollment events, mail-channel guard, routes/auth, per-period flag uniqueness.

### Changes
- **Channels:** student (portal+email) — password changed, enrollment approved/rejected, all grades complete, enrollment phase opened, new school year started; teacher (portal+email) — new enrollment request, advisory class assigned/changed, password changed. Portal-only — grade submitted/updated, profile/info updated, subject added/removed, phase closed, term changed, account info updated. Admins never receive notifications or emails.
- **Grades-complete dedup:** the service computes "complete" as every subject row for the student having a grade for the current term, then uses `grade_completion_flags` to send the "All Grades Complete" notification exactly once per student+term+school year. Editing an existing grade does not re-send; adding a subject re-arms the flag so it fires again once everything is complete. Grading logic is untouched.
- **UI:** the shared header now shows a bell with an unread badge and a dropdown (latest 8, per-item mark-as-read via open link, "Mark all read"), plus a full `/notifications` history page. Layout/design conventions match the existing futuristic style; no redesign.

### Verification
- `php artisan migrate --force` — both new tables created on the local MySQL DB.
- `php artisan test` — 7/7 passing (2 existing + 5 new).
- `npm run build` — assets compiled cleanly.
- `php artisan view:cache` — all Blade templates compile.
- `vendor/bin/pint` — new files formatted to the Laravel preset.
- HTTP smoke tests (`php artisan serve`): bell + badge render in the header for admin; `/notifications` page 200; opening a notification marks it read and redirects to its target page; "Mark all read" clears the badge (CSRF-safe). Grade submission via the teacher flow created a "Grade Submitted" (portal) + "All Grades Complete" (portal+email, mail logged to `storage/logs/laravel.log`) notification; re-submitting the same grade did not duplicate the complete notification.

### Notes
- No Git operations were performed.
- Emails use the existing `MAIL_MAILER=log`/`array` config; the mail channel is only active when the recipient has an email address.
- Demo smoke-test rows (notifications, grade-completion flags) were removed and the touched demo grade restored after testing.

---



## DMMNHS-V.2.7: Academic Calendar (Student / Teacher / Admin)

**Date:** 2026-08-06

### Files Changed
- `database/migrations/2026_08_06_000001_create_academic_calendar_events_table.php` (new) - `academic_calendar_events` table (id, title, event_date, start_time/end_time, location, short_description, full_description, category, school_year, term, timestamps).
- `app/Models/AcademicCalendarEvent.php` (new) - model with `CATEGORIES` constant (Academic, Exam, Holiday, Event, Activity, Deadline, Other).
- `app/Support/helpers.php` - added `academic_calendar_category_style()` (badge/dot/cell Tailwind classes per category).
- `app/Http/Controllers/AcademicCalendarController.php` (new) - shared Student/Teacher monthly calendar.
- `app/Http/Controllers/Admin/AcademicCalendarController.php` (new) - admin event CRUD + filters + month preview.
- `routes/web.php` - `student.calendar`, `teacher.calendar`, and `admin.academic-calendar` (index/store/edit/update/destroy) routes inside the existing role groups.
- `resources/views/calendar/show.blade.php` (new) - monthly calendar page for students/teachers.
- `resources/views/calendar/partials/month-grid.blade.php` (new) - reusable month grid (today highlight, category-dot badges, clean cells).
- `resources/views/calendar/partials/event-modal.blade.php` (new) - event-details modal incl. "No update yet" state.
- `resources/views/admin/academic_calendar.blade.php` (new) - management page: filter bar, month preview, events table, create modal.
- `resources/views/admin/edit_calendar_event_modal.blade.php` (new) - shared create/edit modal form.
- `resources/views/components/layouts/sidebar.blade.php` - "Academic Calendar" links for admin, teacher, student.
- `tests/Feature/AcademicCalendarTest.php` (new) - 7 tests covering create->student visibility, teacher view, update, delete, validation, school-year filter, future-year reachability/clamping.

### Changes
- **Student & Teacher Calendar:** monthly grid for the CURRENT school year with Prev/Next month navigation; clicking any date opens an event-details modal (all events that day with time/location/description, or a "No update yet" state); today's date is highlighted; cells without events stay clean; category legend included. Navigation is bounded to the current school year plus future school years that already have events.
- **School-year logic reuse:** the calendar reuses `settings.current_school_year`/`current_term` (the system's semester concept, renamed to Term) and the same `YYYY-YYYY` year format; no separate school-year logic was created. Months are mapped to a school year using the Philippine June-May boundaries. New school years automatically appear once events are posted for them.
- **Admin Calendar Management:** form-based add/edit (title, date, start/end time, location, short & full description, category, school year, term) via modals; View/Edit/Delete actions; filters by school year, term, and category; a month preview grid with event summary; events appear on student/teacher calendars immediately.

### Verification
- `php artisan migrate` - `academic_calendar_events` table created (local MySQL + sqlite test DB).
- `php artisan test` - 14/14 passing (7 prior + 7 new calendar tests).
- `npm run build` - assets compiled cleanly (Tailwind v4 picks up the new view classes).
- `php artisan view:cache` - all Blade templates compile.
- `vendor/bin/pint` - all files formatted to the Laravel preset.
- HTTP smoke tests (`php artisan serve`): admin index + month preview nav + category filter + edit-partial fetch OK; teacher & student calendar pages render with seeded SY 2025-2026 demo events; navigation to a future school year with events works.

### Notes
- No Git operations were performed.
- The admin form labels the semester field "Term" (Term 1/2/3) to stay consistent with the existing settings/rename-semester-to-term logic.
- 4 demo calendar events were seeded for SY 2025-2026 so the calendar is non-empty on the demo DB.
- A temporary `_smoke_calendar.php` script was used for the HTTP smoke run and deleted afterwards.



---

## DMMNHS-V.2.8 — Targeted Announcement System

### Summary
- Implemented a full targeted announcement system: admin CRUD plus management filters, role/grade/section/student/teacher audience targeting, dashboard feed cards with priority/unread badges and a detail modal, mark-read tracking, and automatic expiration. Added the corresponding `changes.md` entry.

### Key Decisions & Constraints
- Do not modify unrelated features. Reused existing roles, `students.grade_level`, `teachers.advisory_class`, approved `enrollment_requests`, and `settings` (school year / term). No Git operations performed.
- A student's section is derived from the approved-enrollment adviser's `advisory_class`, falling back to any subject teacher's `advisory_class`; logic lives in `App\Services\AnnouncementService::studentSection()`.
- Targeting is `target_role` (`all` | `students` | `teachers` | `admins`) combined with refinement rows in `announcement_audiences` (`target_type`: `grade_level` | `section` | `student` | `teacher`, `target_value`). Student matching is additive (grade OR section OR student).
- Feed visibility: `published`, non-expired, current school year + current term, `publish_date <= today`. Expired items remain in the DB for admin history.
- Read state: `announcement_reads` unique pair (`announcement_id`, `user_id`); marked read when the detail modal opens.
- Attachments are stored on the `public` disk under `announcements/`; replaced or removed via the `remove_attachment` flag; deleted on destroy.
- `App\Models\Announcement::setExpirationDateAttribute()` normalizes an empty string to `null` — required because an empty string breaks `whereDate` expiration filters (caught in tests).

### Files
- `database/migrations/2026_08_06_000002_create_announcements_table.php`
- `database/migrations/2026_08_06_000003_create_announcement_audiences_table.php`
- `database/migrations/2026_08_06_000004_create_announcement_reads_table.php`
- `app/Models/Announcement.php` — constants, relations, `isPublished()`, `hasExpired()`, `priorityLabel()`, `audienceBaseLabel()`, expiration mutator
- `app/Models/AnnouncementAudience.php`
- `app/Models/AnnouncementRead.php`
- `app/Services/AnnouncementService.php` — shared feed/targeting/read logic, `studentSection()` fallback derivation
- `app/Support/helpers.php` — added `announcement_priority_style($priority, $kind)` returning `badge`/`accent` Tailwind classes (normal=sky, important=amber, urgent=red)
- `app/Http/Controllers/Admin/AnnouncementController.php` — index with status/audience/SY/term/date-from/date-to/search filters, store, edit (AJAX partial), update (audience replace, attachment replace/remove), toggle-status, destroy; helpers `years()`, `sectionsList()`, `studentsList()`, `teachersList()`, `filterQuery()`
- `app/Http/Controllers/AnnouncementController.php` — `index()` via `AnnouncementService::feed()`, `markRead()` JSON returning `{ok: true, unread: N}`
- `app/Http/Controllers/Student/DashboardController.php` and `app/Http/Controllers/Teacher/DashboardController.php` — now pass `$announcements` + `$announcementUnread`
- `routes/web.php` — 8 announcement routes (listed by `php artisan route:list`)
- `resources/views/admin/announcements.blade.php` — filters, table, create modal, AJAX edit fetch, view modal, `announcementsData` JSON
- `resources/views/admin/edit_announcement_modal.blade.php` — hideable grade/section dropdown groups per role
- `resources/views/announcements/index.blade.php`, `resources/views/announcements/feed.blade.php`, `resources/views/announcements/announcement-modal.blade.php` — full UI; exposes `openAnnouncement()` / `markAnnouncementRead()` / `updateAnnouncementBadge()`
- `resources/views/components/layouts/app.blade.php` — added CSRF meta tag
- `resources/views/components/layouts/sidebar.blade.php` — "Announcements" sidebar links for admin/teacher/student plus bell icon
- `resources/views/student/dashboard.blade.php` and `resources/views/teacher/dashboard.blade.php` — feed partial included at the bottom (after the closing `</x-layouts.app>` tag)
- `tests/Feature/AnnouncementTest.php` — 17 tests; full suite 31/31 passing with 118 assertions

### Verification
- `php artisan test` — 31/31 passing (118 assertions).
- Pint passed on all touched files; `npm run build` clean; `php artisan view:clear` and `php artisan route:list` verified.
- Manual CRUD verified against the demo MySQL DB (create with grade_level audience, edit prefilled checkboxes, toggle-status, update via PUT, mark-read endpoint unread 1 → 0).
- Demo: MySQL `student_portal` on http://127.0.0.1:8000; logins `admin`/`Test1234!`, `juan.dela.cruz`/`Demo123!` (grade 7 student); settings are term=2, SY=2025-2026.
- Note: this V.2.8 entry was appended using a single-quoted PowerShell here-string to preserve backticks, replacing the corrupted `Add-Content`-based entry.

---

## DMMNHS-V.2.9 — Digital Student ID with Scannable QR Code and Dynamic Verification

### Summary
- Implemented a full Digital Student ID feature: a dedicated student page showing a modern ID card with photo, stable student ID number, grade/section/strand, current SY + term and live status, a scannable QR code, a public verification endpoint (`/verify/student/{token}`) with dynamic states, and an admin management page.

### Key Decisions & Constraints
- No hardcoded academic info. Everything is derived live from the existing systems: `students.grade_level`, section from approved-enrollment adviser `advisory_class` (reusing `AnnouncementService::studentSection()`), current SY/term from the `settings` singleton, and enrollment status from approved `enrollment_requests` rows. No duplicate systems were created.
- QR encodes only the secure verification URL (`/verify/student/{token}`); the token is a 64-char random string (`Str::random(64)`) stored on `students.id_token`, never the DB id. No passwords/grades/contacts/addresses in the QR or verification payload.
- Verification states are computed live: invalid token -> "Invalid ID" (no student info exposed); user/student `inactive` -> "ID Inactive"; no approved enrollment -> "Not Currently Enrolled"; otherwise -> "Verified Student". Changing SY/term or enrollment immediately changes the verification result.
- Student ID number (`students.student_id_no`) is stable and derived from current school year start + student id (e.g. `2025-00002`); backfilled for existing students in the migration and generated lazily for new ones.
- Student photos uploaded to the `public` disk under `student-photos/` (reuses the announcement attachment pattern); old file deleted on replace.
- QR library added: `simplesoftwareio/simple-qrcode` 4.2.0 (GD present). The QR is rendered as an inline SVG (XML prolog stripped).

### Files
- `database/migrations/2026_08_06_000005_add_digital_id_fields_to_students_table.php` — adds `student_id_no`, `id_token`, `id_token_generated_at`, `photo` + backfills existing students
- `app/Models/Student.php` — new fillable fields + `id_token_generated_at` datetime cast
- `app/Services/DigitalIdService.php` — `ensureStudentIdNo()`, `ensureToken()`, `regenerateToken()`, `revokeToken()`, `findByToken()`, `sectionFor()`, `advisoryParts()` (handles `Grade 11-Rizal (Academic)` and legacy `7-A`), `statusFor()` (valid/inactive/not_enrolled), `verificationData()` (minimal public payload), `verificationUrl()`, `qrSvg()`
- `app/Http/Controllers/Student/DigitalIdController.php` — `show()` (Digital ID page) and `uploadPhoto()`
- `app/Http/Controllers/StudentVerificationController.php` — public `show(token)`, returns invalid state for unknown tokens without exposing student data
- `app/Http/Controllers/Admin/DigitalIdController.php` — `index()` (search + status filter + manual pagination), `show()` (single card preview), `regenerate()`, `revoke()`
- `routes/web.php` — public `verify.student`; student `digital-id` + `digital-id.photo`; admin `digital-ids`, `digital-ids.show`, `digital-ids.regenerate`, `digital-ids.revoke`
- `resources/views/digital_id/card.blade.php` — shared ID card partial (logo, photo/initials, name, ID no, grade/section/strand, SY/term, status pill, QR)
- `resources/views/student/digital_id.blade.php` — Digital ID page with status/verification-link/photo-upload panels
- `resources/views/verify/student.blade.php` — responsive public verification page with the 4 states
- `resources/views/admin/digital_ids.blade.php` — searchable admin list with status + token columns and actions
- `resources/views/admin/digital_id_show.blade.php` — admin single-ID card preview
- `resources/views/components/layouts/sidebar.blade.php` — added "Digital ID" (student) and "Student Digital IDs" (admin) links
- `composer.json` / `composer.lock` — added `simplesoftwareio/simple-qrcode`
- `tests/Feature/DigitalIdTest.php` — 12 tests covering card render, token/ID generation, all 4 verification states, dynamic SY/term reflection, invalid-token privacy, admin search/regenerate/revoke, photo upload

### Verification
- `php artisan test` — full suite 43/43 passing (176 assertions); DigitalIdTest 12/12.
- Pint fixed on new/changed files; `npm run build` clean.
- Browser smoke-tested on the demo MySQL DB: student `juan.dela.cruz` Digital ID renders card + QR (200); `/verify/student/{token}` shows "Verified Student" with name/ID/SY; invalid token shows "Invalid ID" with no student info leaked; admin `/admin/digital-ids` lists students with status + regenerate actions.

### V.2.9.1 — Fix: duplicate toast/modal notifications on page load
- Root cause: `resources/views/components/notice.blade.php` registered BOTH a `DOMContentLoaded` listener AND a `setTimeout(200)` fallback whenever `showToast`/`showModal` was not ready when the inline script ran. After exposing these helpers on `window` (V.2.9), both fallbacks fired on the login page (e.g. wrong password), showing the toast twice.
- Fix: added a `shown` guard flag so the notification is displayed exactly once (first fallback that succeeds wins; the other becomes a no-op). Applies to both the toast and the modal blocks.
- Verified: rendered HTML of a failed-login page contains the guarded script; full suite still 43/43 passing (176 assertions). No rebuild needed (Blade-only change).

---

## DMMNHS-V.2.10 — Admin Message Moderation System

### Summary
- Extended the existing Teacher/Student -> Admin messaging (the `contact_messages` feature) with a full moderation workflow: authenticated student/teacher message sending with a server-enforced 3-per-day limit, an Admin Message Center inbox (PENDING -> VALID/INVALID) with filters and summary cards, a blocked-sender management area, 1-day retention for invalid messages, and generic "Message received!" sender notifications that never reveal the moderation decision.

### Key Decisions & Constraints
- No separate messaging system was created. The existing `contact_messages` table was extended with `user_id`, `sender_role`, `status`, `moderated_at`, `expires_at`, `archived_at`; the anonymous public Contact Us form still works (guest messages have null user_id/sender_role and land in the same inbox as "Guest / Visitor").
- The 3-message daily limit is enforced in `App\Services\MessageService` (count of the user's messages with created_at >= start of day), throwing a validation error on the 4th attempt, so it cannot be bypassed from the UI.
- Blocking is a separate `message_sender_blocks` table and is independent from moderation: marking invalid never auto-blocks, and blocking never changes a message's status.
- Invalid messages store an explicit `expires_at = now()->addDay()` timestamp; `pruneExpiredInvalid()` archives (sets archived_at) any invalid message whose expiry passed, and runs at the top of the admin Message Center index so expired items disappear without a scheduled task. Valid messages stay until manually deleted.
- Sender notifications use the existing Laravel notification system (`NotificationService::messageReceived()`) with the exact generic copy "Message received!" - identical for valid and invalid, no approval/decision data exposed. The sender's own message list shows no moderation status either.
- Admin UI uses the existing futuristic portal design: summary cards (Pending Review / Valid / Invalid / Blocked Senders + "X waiting for review"), an inbox table with PENDING=yellow / VALID=green / INVALID=red / BLOCKED=dark badges, a message-detail modal, and styled confirmation dialogs (via the existing data-confirm helper) for mark-invalid, block, and unblock. Blocking collects an optional reason in its own modal.

### Files
- `database/migrations/2026_08_07_000001_add_moderation_fields_to_contact_messages_table.php` - moderation columns + indexes
- `database/migrations/2026_08_07_000002_create_message_sender_blocks_table.php` - blocked-sender tracking
- `app/Models/ContactMessage.php` - new fillable fields/casts, status constants, user() relation
- `app/Models/MessageSenderBlock.php` - block model with isActive()
- `app/Services/MessageService.php` - DAILY_LIMIT=3, remainingToday/limitReached/isBlocked/activeBlock/submit (throws on block or over-limit), moderate (sets status+moderated_at, expires_at for invalid), blockSender/unblock, pruneExpiredInvalid
- `app/Services/NotificationService.php` - added `messageReceived()` (generic "Message received!")
- `app/Http/Controllers/MessageController.php` - shared student/teacher send page + store
- `app/Http/Controllers/Admin/MessageCenterController.php` - index (summary + filters: status/role/blocked/date/q), markValid, markInvalid, blockSender, destroy, blockedSenders, unblock
- `routes/web.php` - shared `messages.create`/`messages.store` (role:student,teacher) + admin message-center group
- `resources/views/messages/create.blade.php` - send page with remaining limit / reached-limit / blocked states and the sender's own message list (no moderation status)
- `resources/views/admin/message_center.blade.php` - moderation inbox (summary cards, filters, table, detail modal, block modal)
- `resources/views/admin/blocked_senders.blade.php` - blocked users list with reason/blocker/date + unblock
- `resources/views/components/layouts/sidebar.blade.php` - "Message Center" (admin) and "Message Admin" (student/teacher) links
- `tests/Feature/MessageModerationTest.php` - 14 tests

### Verification
- `php artisan test` - full suite 57/57 passing (255 assertions); MessageModerationTest 14/14.
- Pint clean on all new/changed files (accidental repo-wide Pint cosmetics on unrelated pre-existing files were reverted with git checkout to keep the change surface clean); `npm run build` clean.
- Browser/HTTP smoke-tested on the demo MySQL DB: student `juan.dela.cruz` message page shows "3 of 3 messages remaining today" and drops to "2 of 3" after sending; admin `/admin/message-center` lists it as PENDING with summary cards; "Mark Valid" -> VALID badge and the student receives the generic "Message received!" notification; blocking with reason appears in Blocked Senders and the student then sees "You are currently unable to send messages to the administration." and cannot submit; unblock restores sending.

---

## DMMNHS-V.2.10.1 — Contact Us Consolidation

### Summary
- Consolidated the student/teacher message form INTO the existing Contact Us page, eliminating the standalone "Message Admin" page and its navigation. Logged-in students/teachers now get a pre-filled, read-only Name/Email (from the portal account) so trolls cannot impersonate; their submissions are attributed to the account and counted against the server-enforced 3/day limit, and any name/email typed into the form is ignored on the server for those roles (anti-hack).

### Key Decisions & Constraints
- Single message form: the public Contact Us form (`/contact`) is now the only way for students/teachers to message the administration. Guest/visitor submissions keep the manual Name/Email fields and remain anonymous (null user_id/sender_role) in the same admin inbox.
- For authenticated students/teachers the form pre-fills and locks Name/Email (readonly inputs from `auth()->user()`), shows a "You have X of 3 messages remaining today" banner, and disables the subject/message/button when the daily limit is reached or the sender is blocked.
- Server-side: `PageController::submitContact()` routes student/teacher submissions through `MessageService::submit()` which (a) attributes via user_id/sender_role, (b) enforces the 3/day limit and block, (c) overrides any submitted name/email with the account values. Guests keep `ContactMessage::create($validated)`.
- Removed the now-redundant pieces: `MessageController`, `resources/views/messages/create.blade.php`, the `messages.create`/`messages.store` routes, and the student/teacher "Message Admin" sidebar entries (admin "Message Center" unchanged).
- `NotificationService::messageReceived()` link repointed from `route('messages.create')` to `route('contact')`.

### Files
- `app/Http/Controllers/PageController.php` - contact() passes sender state (remaining/limit/limitReached/blocked/isSender); submitContact() sends via MessageService for student/teacher, keeps guest path
- `resources/views/contact.blade.php` - single form with pre-filled readonly name/email, remaining-limit banner, reached-limit notice, blocked banner, disabled states
- `app/Http/Controllers/MessageController.php` - DELETED
- `resources/views/messages/create.blade.php` - DELETED
- `routes/web.php` - removed `messages.create`/`messages.store` route group
- `resources/views/components/layouts/sidebar.blade.php` - removed student/teacher "Message Admin" entries
- `app/Services/NotificationService.php` - messageReceived() link -> route('contact')
- `tests/Feature/MessageModerationTest.php` - rewritten to submit via contact.submit/contact; asserts prefill + account attribution over spoofed name/email (14 tests)

### Verification
- `php artisan test` - full suite 57/57 passing (258 assertions); MessageModerationTest 14/14.
- Pint clean on changed files; `npm run build` clean (new class `bg-[#0018f9]/5`).
- Browser/HTTP smoke-tested on the demo MySQL DB: student `juan.dela.cruz` `/contact` shows pre-filled readonly "Wowowin"/`juan.dela.cruz@dmnhs.edu` with "1 of 3 messages remaining today"; submitting with spoofed name/email still stores the account identity (user_id=8, sender_role=student, name "Wowowin") and drops the counter to "0 of 3" with the reached-limit notice + disabled fields; admin `/admin/message-center` lists the new message as PENDING with the account's email; sidebar no longer shows "Message Admin".

---

## DMMNHS-V.2.11 — Requirement & Submission Tracker

### Summary
- Added a complete teacher-driven Requirement & Submission Tracker: teachers create requirements that are automatically assigned to their currently approved/enrolled students only (derived from `enrollment_requests`), students submit or resubmit text/file responses, teachers review, approve, or request revision with feedback, teachers can Bump Reminders to pending students (24-hour cooldown), and students get due-soon/overdue reminders through the existing notification system.

### Key Decisions & Constraints
- Assignment is ALWAYS derived from the existing enrollment system: `RequirementService::assignedStudents(teacherId)` joins `students` -> `enrollment_requests (status='approved')` -> `users`. A teacher can never assign a requirement to unrelated students; students later approved are added automatically (no per-student assignment table).
- Requirements are scoped to the current academic period (`school_year` + `term` from `Setting::find(1)->period()`); the teacher's `advisory_class` string is copied to `requirements.section`. A student can only see requirements for teachers they have an approved enrollment request with, in the current period.
- Per-student state lives on `requirement_submissions` rows; a missing row means "pending". Workflow: `pending -> submitted -> under_review -> approved` or `submitted -> needs_revision -> resubmitted -> under_review`. `resubmitted` is treated like `submitted` for teacher review actions.
- `submission_required` (checkbox on create/edit) distinguishes "students must submit" from "info-only" requirements; info-only requirements show an acknowledgement note instead of the submit form.
- Bump cooldown is 24 hours, tracked on the requirement row (`last_bumped_at`/`last_bumped_by`/`bump_count`). `bumpAll()` reminds only students with no submission row and returns the count; `bumpStudent()` targets a single pending student. Both respect the cooldown and record it.
- Due reminders: on the student index, `RequirementService::notifyDueReminders()` fires "Requirement Overdue" / "Requirement Due Soon" (3-day window) notifications, each deduped while an identical unread title exists (`NotificationService::requirementDueReminder()` LIKE check on `data.title`).
- Notifications reuse the existing DB-notification system; new `kind` values (`'requirement'` -> teal + clipboard-document icon) were added to `resources/views/components/notification-icon.blade.php` and the sidebar icon set.
- Ownership guards: teacher routes 403 unless `requirements.teacher_id` / `submissions.teacher_id` matches the current teacher; student routes 403 unless the student is enrolled under the requirement's teacher.

### Files
- `database/migrations/2026_08_08_000001_create_requirements_table.php` - requirements (teacher_id, title, requirement_type enum, description, due_date, submission_required, attachment(+name), section, school_year, term, status, bump tracking; index [teacher_id, school_year, term])
- `database/migrations/2026_08_08_000002_create_requirement_submissions_table.php` - per-student submissions (status enum, response_text, attachment(+name), feedback, submitted_at, reviewed_at; unique [requirement_id, student_id])
- `app/Models/Requirement.php` - TYPES/STATUS_ACTIVE constants, casts, typeLabel(), hasDueDate()/isOverdue()/isDueSoon(), teacher()/submissions()
- `app/Models/RequirementSubmission.php` - 6 status constants + STATUS_LABELS/STATUS_STYLES, predicates, relations
- `app/Services/RequirementService.php` - BUMP_COOLDOWN_HOURS=24, assignedStudents/assignedStudentIds, effectiveStatus, progress, canBump/bumpCooldownRemaining/recordBump, bumpAll/bumpStudent, notifyDueReminders, canSubmit
- `app/Services/NotificationService.php` - added requirementAssigned, requirementBumped, submissionApproved, submissionNeedsRevision, requirementDueReminder (dedup)
- `app/Http/Controllers/Teacher/RequirementController.php` - index/create/store/show/edit/update/bump/remindStudent/download/destroy
- `app/Http/Controllers/Teacher/SubmissionController.php` - approve/revision/download (owned guard)
- `app/Http/Controllers/Student/RequirementController.php` - index/show/submit/download/downloadSubmission (enrollment guard)
- `routes/web.php` - teacher.requirements.* + teacher.submissions.* + student.requirements.* groups
- `resources/views/teacher/requirements_index.blade.php` - card list with progress bar, bump button/cooldown, pagination
- `resources/views/teacher/requirements_create.blade.php` - create form (type select, due date, attachment, submission_required, approved-student count)
- `resources/views/teacher/requirements_edit.blade.php` - edit form (attachment replace/remove)
- `resources/views/teacher/requirements_show.blade.php` - summary cards, student x status table, approve / revision-feedback modal / remind / download / delete
- `resources/views/student/requirements.blade.php` - card grid with filters (all/pending/overdue/submitted/needs_revision/approved), overdue/due-soon banners
- `resources/views/student/requirements_show.blade.php` - detail + status + submit/resubmit form + info-only note
- `resources/views/components/layouts/sidebar.blade.php` - `'requirement'` icon + teacher/student "Requirements" nav entries
- `resources/views/components/notification-icon.blade.php` - `'requirement'` icon + teal tone
- `tests/Feature/RequirementTrackerTest.php` - 13 tests

### Verification
- `php artisan test` - full suite 70/70 passing (346 assertions); RequirementTrackerTest 13/13.
- Pint clean on all new/changed feature files (pre-existing unrelated files remain untouched).
- `php artisan migrate` applied the two new migrations on the demo MySQL DB.
- Browser/HTTP smoke-tested on the demo MySQL DB: teacher `elena.reyes` creates a requirement (`/teacher/requirements/create` shows "1 approved student(s)"), it appears on `/student/requirements` for the assigned student `juan.dela.cruz` (Wowowin) and NOT for unassigned students; student opens it, submits a text response, sees "Submitted"; teacher's show page lists the Wowowin row and "Approve" returns 302; student then sees "Approved" status. All smoke-test data was cleaned up afterward.

---

## DMMNHS-V.2.12 — Role Separation: System Administrator vs Office Administrator

### Summary
- Split the single legacy `admin` role into two distinct staff roles. **System Administrator** (stored role `system_admin`, path `/admin`, routes `admin.*`) owns portal configuration: manage/create accounts and enrollment settings (term, school year, enrollment phase). **Office Administrator** (new stored role `office_admin`, path `/office`, routes `office.*`) owns the academic/student-facing modules previously bundled under admin: academic calendar, announcements, student digital IDs, teacher advisory assignment, assign class, message center, and a new read-only Requirement & Submission Tracker oversight page.
- Stored role value renamed `admin` -> `system_admin` via a data migration; `users.role` enum widened to `['system_admin','office_admin','teacher','student']`. The existing `users` data is migrated in place (intermediate enum keeps the legacy `admin` value legal until the data row is renamed, because MySQL validates existing rows on every enum MODIFY).

### Key Decisions & Constraints
- Role gating remains the plain enum + `CheckRole` middleware (no spatie package). `CheckRole` is unchanged: wrong role -> `redirect()->route('login')`.
- Old `app/Http/Controllers/Admin/*` namespace was deleted. Controllers physically moved/renamed into `App\Http\Controllers\SystemAdmin\*` (Dashboard, Account, EnrollmentSetting) and `App\Http\Controllers\OfficeAdmin\*` (Dashboard, TeacherAssignment, AcademicCalendar, Announcement, DigitalId, MessageCenter, Requirement).
- `TeacherAssignmentController::parseAdvisory()` and `sectionExistsForGrade()` static helpers moved to the OfficeAdmin controller; they are reused by `Teacher\SubjectController` and `DigitalIdService` (and `SystemAdmin\AccountController` delegates advisory-uniqueness to `sectionExistsForGrade`). All compile-verified via the test suite and HTTP smoke test.
- Office admin Requirement oversight is read-only (index/show/download); teachers keep full management. Views reuse the requirement models/service/constants (`Requirement::STATUS_ACTIVE`, `typeLabel()`, `RequirementSubmission::STATUS_LABELS/STATUS_STYLES`).
- Demo seeder now creates an Office Administrator (`office`/`Office123!`) via `firstOrCreate`; the dev `admin` account's password is untouched by the seeder (only the role value was migrated).
- Auth `dashboardRoute()`, `NotificationService::dashboardLink()` and `AnnouncementService` staff-audience handling updated for the two staff roles. Sidebar shows distinct menus per staff role; header/sidebar role chips render `str_replace('_',' ', ...)` so `system_admin`/`office_admin` display naturally.
- New/updated blades moved to `resources/views/office/*`; `admin` blades updated for System Administrator copy and the Office Admins account tab.
- Test suite updated in place: AnnouncementTest, AcademicCalendarTest, DigitalIdTest, MessageModerationTest now act as office admins and hit `office.*` routes; new `RoleAccessTest` asserts the role-value matrix and per-role route access.

### Files
- `database/migrations/2026_08_09_000001_add_office_admin_role_and_rename_admin_to_system_admin.php` - widens enum (keeps legacy `admin`), migrates `admin` -> `system_admin` data, then narrows to final enum; down() collapses both staff roles back to `admin`
- `app/Models/User.php` - isSystemAdmin()/isOfficeAdmin()/isAdmin() (alias) helpers
- `app/Http/Controllers/Auth/AuthController.php` - dashboardRoute() matches the two staff roles
- `app/Http/Controllers/SystemAdmin/{DashboardController,AccountController,EnrollmentSettingController}.php` - moved/renamed, last-system-admin guard, role tabs incl. Office Admins
- `app/Http/Controllers/OfficeAdmin/{DashboardController,TeacherAssignmentController,AcademicCalendarController,AnnouncementController,DigitalIdController,MessageCenterController,RequirementController}.php` - moved/renamed + new read-only Requirement oversight (index/show/download)
- `routes/web.php` - `admin.` (role:system_admin) and `office.` (role:office_admin) route groups
- `app/Http/Requests/CreateUserRequest.php` - role rule now `in:system_admin,office_admin,teacher,student`
- `database/seeders/DatabaseSeeder.php` - seeds office admin demo account
- `app/Services/AnnouncementService.php`, `app/Services/NotificationService.php` - staff-role handling
- `app/Http/Controllers/Teacher/SubjectController.php`, `app/Services/DigitalIdService.php` - import helpers from `OfficeAdmin\TeacherAssignmentController`
- `resources/views/office/*` - moved academic/office blades (academic_calendar, announcements, assign_class, blocked_senders, digital_ids, digital_id_show, edit_advisory_modal, edit_announcement_modal, edit_calendar_event_modal, message_center, teacher_advisory) + new `dashboard.blade.php` and `requirements.blade.php`/`requirements_show.blade.php`
- `resources/views/admin/*` - dashboard (System Administrator copy + Office Admins stat), accounts (Office Admins tab + role labels), create/edit account role options, enrollment_settings (advisory link removed)
- `resources/views/components/layouts/sidebar.blade.php` - distinct staff menus, `requirement` icon; `app.blade.php`/`header.blade.php` - guest-safe rendering fixes
- `resources/views/about.blade.php` (and contact/password) - role match includes both staff roles
- `tests/Feature/{RoleAccessTest.php,AnnouncementTest.php,AcademicCalendarTest.php,DigitalIdTest.php,MessageModerationTest.php}` - role/routing updates + new access tests

### Fixes Along the Way
- `resources/views/components/layouts/sidebar.blade.php` profile chip crashed on public pages (`/about`, `/contact`) with "Attempt to read property name on null" because it rendered `auth()->user()->name` unguarded for guests. Wrapped in `@auth` (pre-existing latent bug surfaced during HTTP smoke testing).

### Verification
- `php artisan test` - full suite 76/76 passing (380 assertions); RoleAccessTest covers the new role matrix.
- Pint clean on all touched PHP files (ran `vendor/bin/pint` on the refactor surface).
- `php artisan migrate --force` applied on demo MySQL; verified enum is now `enum('system_admin','office_admin','teacher','student')` and the legacy `admin` user row reads `system_admin`; `db:seed` added office admin (`office`/`Office123!`).
- `npm run build` clean after blade changes.
- Real HTTP smoke test on `php artisan serve` with cookie sessions: office admin reaches all 8 `office.*` pages (200); system admin reaches `/admin/*`; teacher/student reach their dashboards; every wrong-role access redirects to the login page (`/`). Public `/about`/`/contact` return 200 after the sidebar fix.


## DMMNHS-V.2.13 — Important Dates Dashboard Widget (Aggregation)

### Summary
- Added an aggregation-layer **Important Dates** widget that surfaces the user's nearest upcoming dates directly on the student, teacher, and office administrator dashboards. It queries **existing records only** — Academic Calendar events (filtered to current school year/term, date >= today), Requirement & Submission Tracker deadlines (per user), and reflects the current term/school year from Setting — with a compact 5-item list, urgency styling, and a role-aware "View All" page. No new calendar/event/deadline records are created anywhere; the widget is purely read-only aggregation of existing systems.

### Key Decisions & Constraints
- Aggregation lives in a dedicated service App\Services\ImportantDatesService with orUser(User ) plus per-role builders (studentItems, 	eacherItems, officeItems) and shared calendarEvents/equirementItem/item helpers. Calendar events are filtered by the current school_year + 	erm (single source of truth: Setting::period()) and event_date >= today.
- Student requirements are resolved through RequirementService's assignment semantics: only teachers whose enrollment request is pproved, scoped to current school year/term, status = active, due_date set and not in the past, and no submission or a 
eeds_revision submission. Teacher items are teacher-owned only; office admin sees school-wide deadlines.
- Item model is a plain stdClass (	ype/title/subtitle/date/relative/urgency/url/detail) so the widget is framework-agnostic. Urgency buckets: urgent (<= 1 day), soon (<= 3 days), 
ormal; relative labels Today / Tomorrow / "In N days" / formatted date.
- Widget component <x-important-dates> takes items, iew-all-url, limit (default 5), renders an icon per type, urgency dot/text/date styles, and a "View All" link only when there are more items than the limit; empty state reads "No upcoming dates." All styling reuses the existing futuristic Tailwind design language (gradient #0a1633 → #164aa8, x-card).
- One route per role: student.important-dates, 	eacher.important-dates, office.important-dates — all pointing at the single ImportantDatesController@index; the view-all page supplies a per-role back URL fallback. Calendar drill-down links reuse the existing role calendar routes (teacher/student month view, office academic calendar).
- Tests mirror the live-data situation (demo MySQL has no current-term events and no requirements), so the widget renders its empty state on the live DB while the feature tests exercise the populated states.

### Files
- pp/Services/ImportantDatesService.php - new aggregation service (constants TYPE_ACADEMIC_EVENT/TYPE_REQUIREMENT, URGENT_DAYS/SOON_DAYS)
- pp/Http/Controllers/ImportantDatesController.php - view-all page controller (single index())
- outes/web.php - important-dates route added to the student., 	eacher., and office. groups
- esources/views/components/important-dates.blade.php - dashboard widget component (items, view-all-url, limit)
- esources/views/important-dates/index.blade.php - role-aware View All page
- pp/Http/Controllers/{Student,Teacher,OfficeAdmin}/DashboardController.php - pass importantDates from pp(ImportantDatesService::class)->forUser(auth()->user())
- esources/views/{student,teacher,office}/dashboard.blade.php - render <x-important-dates> (student grid, teacher after Assessment Scores, office after quick actions)
- 	ests/Feature/ImportantDatesTest.php - 12 feature tests (visibility, filtering, role isolation, ordering, urgency/relative labels, view-all, gating, empty state)

### Verification
- php artisan test - full suite 88/88 passing (424 assertions) after the fix; new ImportantDatesTest 12/12 (44 assertions).
- Fix along the way: merging a base Support\Collection into an Eloquent collection threw Call to undefined method stdClass::getKey(); calendarEvents() now returns collect(->map(...)) so the merge in orUser operates on a base collection.
- endor/bin/pint clean on the touched PHP files (removed an unused import); 
pm run build clean.
- HTTP smoke test against the demo MySQL DB (throwaway test, removed after): office/teacher/student dashboards and all three important-dates pages return 200; service returns 0 items for each role on live data (no current-term events, no requirements) and the widget renders its empty state — expected, not an error.

### Notes
- Deliberately no sidebar link changes: the requirement was role-aware content, and the sidebar already carries the relevant destinations (office Requirements, calendars).
- "View All" link is suppressed when all items fit within the widget limit (count > limit).

---

## DMMNHS-V.2.14 — Teacher Grade Submission Monitor

### Summary
- Added a monitoring layer over the existing grading module for the Office Administrator: a **Grade Submission Monitor** page (filterable unit list, summary cards, completion bar, deadline configuration, single/bulk reminders), a read-only **My Grade Submissions** page for teachers, and a **Grade Submission Progress** widget on the office dashboard. The feature derives status dynamically from existing records — no duplicate grading data is created.

### Key Decisions & Constraints
- Monitoring is purely read-only over the existing `grades` table. A submission unit = (teacher, `subject_name`, term). A unit is **Submitted** when every approved student (via `enrollment_requests.status='approved'`) has a `grades` row for that subject/quarter (`quarter` = `Term N`, `date_submitted` max). Otherwise **Pending**, and **Late** only when a pending unit's deadline has passed. No `grades` rows are ever written by this feature.
- Deadline configuration lives in a new table only: `grade_submission_deadlines` (school_year, term, subject_name, deadline). `subject_name=''` is the global default for all subjects; a subject-specific row overrides the global for that subject. Unique key `unique_grade_deadline` on (school_year, term, subject_name). Model constant `GradeSubmissionDeadline::ALL_SUBJECTS`.
- Reminders go through the existing NotificationService (portal + email), one notification per teacher per remind action (Remind All groups pending/late units by teacher). Notification title `Grade Submission Reminder`, action links to the teacher's `teacher.grade-submissions` page.
- Filters on the office page: school year (default `Setting::current_school_year`), term (default `Setting::current_term`), grade level & section (parsed from `teachers.advisory_class` via `TeacherAssignmentController::parseAdvisory`), teacher, subject, status. Summary card buckets ignore the status filter (late > pending > submitted priority).
- Teacher page shows the teacher's own units with per-unit progress bars, status badges, deadline, and a link into the existing Grades Overview.

### Files
- `database/migrations/2026_08_10_000001_create_grade_submission_deadlines_table.php` — new deadlines table.
- `app/Models/GradeSubmissionDeadline.php` — model with `isGlobal()` helper.
- `app/Services/GradeSubmissionMonitorService.php` — monitoring core: `units()`, `summary()`, `completion()`, `statusLabel()`, option lists, deadline resolution.
- `app/Services/NotificationService.php` — added `gradeSubmissionReminder()` (portal + email).
- `app/Http/Controllers/OfficeAdmin/GradeSubmissionMonitorController.php` — index + deadline store/update/destroy + remind/remindAll.
- `app/Http/Controllers/Teacher/GradeSubmissionController.php` — teacher's own view.
- `routes/web.php` — office group: GET `/grade-submissions`, POST/PUT/DELETE `/grade-submissions/deadlines`, POST `/grade-submissions/remind`, POST `/grade-submissions/remind-all`; teacher group: GET `/grade-submissions`.
- `resources/views/office/grade_submission_monitor.blade.php`, `resources/views/teacher/grade_submissions.blade.php`, `resources/views/components/grade-submission-progress.blade.php`.
- `resources/views/office/dashboard.blade.php` — widget next to Important Dates (2-column grid).
- `resources/views/components/layouts/sidebar.blade.php` — office "Grade Submissions" (tick icon) and teacher "Grade Submissions" (clipboard icon) entries.
- `tests/Feature/GradeSubmissionMonitorTest.php` — 11 feature tests.

### Verification
- `vendor/bin/phpunit --filter=GradeSubmissionMonitorTest` — 11/11 passing (47 assertions). Note: `assertDontSee('Late')` is unreliable on the office page because the "Late" summary card label is always present; tests assert on the `(overdue)` marker instead.
- `php artisan test` — full suite 99/99 passing (471 assertions).
- `vendor/bin/pint --dirty` — applied style fixes to the monitor service; tests re-run green after.
- `npm run build` — clean.
- HTTP smoke test against the demo MySQL DB: applied the new migration live, office login (`office` / `Office123!`) renders the monitor page (summary cards, Remind All, deadlines panel) and dashboard widget (`Grade Submission Progress`), teacher login (`elena.reyes` / `Demo123!`) renders My Grade Submissions with status counts; both new sidebar entries and their icons render.

### Notes
- Default demo login passwords: office `office`/`Office123!` (DatabaseSeeder), demo users `*`/`Demo123!` (DemoSeeder); login uses username, not email.
- Status is derived at render time — a unit flips Pending→Late automatically the day after its deadline, and Submitted units never turn Late.

---

## DMMNHS-V.2.15 - Teacher Workload Dashboard

**Date:** 2026-08-06

### Summary
Replaced the teacher dashboard with a full workload dashboard: an at-a-glance, read-only aggregation of everything a teacher is responsible for - summary cards, today's schedule/workload, upcoming deadlines (requirements + grade submission), pending requirements with progress, grade submission progress, a class summary, recent activity, and quick actions - all computed on the fly from existing data sources (no new tables, no duplicate tracking).

### Key Decisions & Constraints
- Pure aggregation service, no writes. Data sources (all existing): enrollment_requests (approved students, status literal 'pending'), subjects (distinct subject names per student), grades (quarter = 'Term N'), equirements + equirement_submissions (via RequirementService::progress()), grade_submission_deadlines, 	eacher_subjects (classes today), cademic_calendar_events, user 
otifications (unread count), AnnouncementService::feed().
- TeacherWorkloadService::forUser() returns a single object with summary (students, advisory_sections, subjects_handled, classes_today, pending_requirements, pending_grade_submissions, unread_messages, upcoming_deadlines), 	odayWorkload, upcomingDeadlines, pendingRequirements, gradeUnits + gradeCompletion (reuses GradeSubmissionMonitorService::units/completion), classSummary, ecentActivity (limit 10), quickActions (5 links to existing routes).
- Grade-submission deadline label uses GradeSubmissionDeadline::ALL_SUBJECTS for the global default ("Grade Submission").
- Empty states required and present: "No pending workload.", "No upcoming deadlines.", "No pending requirements.", "No classes assigned yet.", "No recent activity."
- Controller passes $workload alongside the existing 	eacher, dvisory, importantDates, nnouncements, nnouncementUnread.

### Files
- pp/Services/TeacherWorkloadService.php - new aggregation service (workload core).
- pp/Http/Controllers/Teacher/DashboardController.php - now passes $workload.
- esources/views/teacher/dashboard.blade.php - rewritten: hero (period + advisory), 8 summary cards, privacy notice, Today's Workload, Upcoming Deadlines, Pending Requirements, Grade Submission Progress, Class Summary, Recent Activity, Quick Actions, important dates + announcements feed.
- 	ests/Feature/TeacherWorkloadTest.php - 13 feature tests.

### Verification
- endor/bin/phpunit --filter=TeacherWorkloadTest - 13/13 passing (60 assertions). Two pitfalls fixed: 	eacher_subjects has a unique (teacher_id, subject_name) constraint so fixtures must not duplicate Mathematics; <x-card> HTML-escapes titles so tests must assert Today&#039;s Workload (escaped needle) not the raw apostrophe.
- php artisan test - full suite 112/112 passing (531 assertions).
- endor/bin/pint - fixed pp/Services/TeacherWorkloadService.php; tests re-run green.
- 
pm run build - clean.
- HTTP smoke test against the demo MySQL DB: teacher login (elena.reyes / Demo123!) renders every dashboard section with live data; office (office / Office123!) and student (juan.dela.cruz / Demo123!) dashboards still render (200).

### Notes
- The dashboard shows empty states only when there is genuinely no data; live teacher data renders populated cards.
- Login uses username, not email. Demo passwords: office Office123!, others Demo123!.

---

## DMMNHS-V.2.16 - Student Timeline (My Academic Journey)

**Date:** 2026-08-06

### Summary
Added a student-only, read-only chronological view of a student's academic history, dynamically aggregated on the fly from existing portal records (enrollment, teacher assignment, grades, assessments, requirements, notifications, academic calendar, announcements, digital ID) - newest first, filterable and searchable. No new tables and no duplicate activity logging.

### Key Decisions & Constraints
- Pure aggregation service, no writes. Data sources (all existing): users.created_at (account activated), students.id_token_generated_at (digital ID generated), enrollment_requests.date_requested (enrollment submitted) with approval/rejection timestamps derived from user notifications (title 'Enrollment Approved' / 'Enrollment Rejected', fallback to date_requested), subjects.created_at (teacher assigned), requirements + requirement_submissions (assigned/submitted/approved/revision keyed on reviewed_at), grades join subjects (grade released), assessment_scores (activity/quiz/exam recorded), user notifications ('All Grades Complete' -> Semester Completed, 'New Term Started', 'New School Year'), academic_calendar_events (past events for the current S.Y./term), AnnouncementService::feed() (published/current announcements).
- Categories (service constants): Academic, Requirements, Grades, Enrollment, Documents, Activities. Filterable by school year, term, category, date range, plus free-text search across title/detail/school year.
- StudentTimelineService::forUser() returns events newest-first; recent() is used by the dashboard widget (latest 5); relativeLabel() renders Today/Yesterday/N days ago/Month d, Y.
- Read-only, student-role gated via existing role middleware; events derived per-source because most legacy tables lack updated_at/created_at columns.
- Empty state: "My academic journey has not started yet."

### Files
- app/Services/StudentTimelineService.php - new aggregation service (timeline core).
- app/Http/Controllers/Student/TimelineController.php - new controller (index with filters/search).
- routes/web.php - GET /timeline (student.timeline) in the student role group.
- resources/views/student/timeline.blade.php - new view: hero card, filter form, vertical timeline, detail modal, empty state.
- app/Http/Controllers/Student/DashboardController.php - now passes recentTimeline.
- resources/views/student/dashboard.blade.php - added Recent Academic Activity widget (latest 5).
- resources/views/components/layouts/sidebar.blade.php - added Timeline nav entry.
- tests/Feature/StudentTimelineTest.php - 12 feature tests.
- tests/Feature/ImportantDatesTest.php - 2 tests switched to component-string assertions after the new widget surfaced requirement titles.

### Verification
- php artisan test - full suite 124/124 passing (595 assertions).
- vendor/bin/pint - clean on all new/changed files.
- npm run build - clean.
- HTTP smoke test against the demo MySQL DB: student (juan.dela.cruz / Demo123!) renders /student/timeline (200, "My Academic Journey" + events) and the dashboard Recent Academic Activity widget; teacher (elena.reyes) is redirected (302) from /student/timeline (role-gated).

### Notes
- Timeline is exclusive to the student role; teacher/office/system users cannot access it.
- Login uses username, not email. Demo passwords: office Office123!, others Demo123!.

---

## DMMNHS-V.2.17 - Modern Landing Page

**Date:** 2026-08-06

### Summary
Replaced the root `/` (previously the login form) with a public marketing landing page for Don Mariano Marcos National High School. The login page moved to `GET /login` (route name `login`), so all existing auth redirects and role middleware remain unchanged. Landing page is fully responsive, animated (IntersectionObserver reveals + counting stats, `prefers-reduced-motion` fallbacks), and pulls live data from settings, announcements, academic calendar events, and portal statistics.

### Key Decisions & Constraints
- Landing page is public and self-contained (no auth middleware); login stays the single entry point for authentication.
- Design reuses the portal design system: glassmorphism cards, `x-decorative-background`, gradients (`#070d1f`/`#0a1633`/`#0d2450`/`#164aa8`) with accents `#38bdf8`, `#2563eb`, `#0b3ef2`.
- Sections: sticky nav (Home/About/Features/Announcements/Contact + always-visible Login), hero with CTA, About, 8 feature cards, 4 role cards, portal preview placeholders, upcoming events preview, latest announcements (3 published/current, "View More" -> login for now), system status card (online pulse + S.Y./Term/enrollment from settings), animated statistics (students/teachers/programs/announcements/requirements), contact, footer.
- Controller queries live data: `Setting::find(1)?->period()` for status; distinct `subjects.subject_name` for program count; published announcements; all `RequirementSubmission` rows for "Requirements Processed".
- Settings singleton, announcement, and academic calendar event sources are the same models already used by the rest of the portal.

### Files
- app/Http/Controllers/LandingPageController.php - new controller for `/` (passes systemStatus, stats, announcements, upcomingEvents).
- routes/web.php - `GET /` -> LandingPageController@index (name `home`); `GET /login` -> existing AuthController@showLoginForm (name `login`, was previously `/`).
- resources/views/landing.blade.php - new standalone landing page with all sections and inline animation JS.
- tests/Feature/LandingPageTest.php - new; 10 feature tests covering rendering, login navigation, auth redirects, settings-driven status, announcements filtering, upcoming events, live statistics, empty state.
- tests/Feature/ExampleTest.php - now uses RefreshDatabase (because `/` queries the database).

### Verification
- php artisan test - full suite 134/134 passing (656 assertions), including the 10 new landing page tests.
- vendor/bin/pint - clean on all new/changed files.
- npm run build - clean.
- php artisan view:cache - clean.
- HTTP smoke test against the demo MySQL DB: `/` renders the landing page (200, "Grade Management Portal"); `/login` still renders the login form (200, "Login to Your Account"); auth redirect for unauthenticated /student/dashboard still targets /login.

### Notes
- Login page itself was not modified; only the route moved from `/` to `/login`.
- Landing page preview/role/contact sections are placeholders; announcements and events previews are live data.
- Login uses username, not email. Demo passwords: office Office123!, others Demo123!.

---

## DMMNHS-V.2.18 - UX Audit: Validation, Confirmations & Error Handling

**Date:** 2026-08-06

### Summary
Comprehensive UX audit across all modules: extended the global form validator to the admin create/edit account forms and password reset, hardened file-upload/delete failure paths so raw exceptions are never shown, and fixed the custom 403/404/500/503 error page component so those pages actually render.

### Key Decisions & Constraints
- Form validation: the existing global `form[data-validate]` custom validator (inline red-border + shake + `.field-error`, no native browser bubbles) is now applied to admin account create/edit and the password reset form, with `data-password-policy` enforcement matching the server rule (>= 8 chars, uppercase or symbol).
- `app.js` validator selector now also matches `input[data-password-policy]` so policy checks apply even to password fields that are not `required`.
- File upload failures (requirement submissions, requirement files, announcements, digital ID photo) redirect back with a friendly toast instead of leaking exception text.
- Destructive deletes (requirement + submissions) catch and report failures, then redirect with a clear error message.
- Custom error pages (`resources/views/errors/403|404|500|503`) reference `<x-errors._error-page>`; the partial must live under `resources/views/components/errors/` for anonymous component resolution — moved from `resources/views/errors/`.

### Files
- resources/views/admin/create_account.blade.php - added `data-validate`, `data-password-policy`, `data-min="8"` to the password field.
- resources/views/admin/edit_account.blade.php - added `data-validate` to the account form and `data-password-policy`/`data-min` to the password reset form.
- resources/js/app.js - validator selector now includes `input[data-password-policy]`.
- app/Http/Controllers/OfficeAdmin/AnnouncementController.php - wrapped attachment upload in try/catch; on failure reports, flashes a friendly error, and rethrows a ValidationException so the form redraws with an inline error.
- app/Http/Controllers/Teacher/RequirementController.php - `destroy()` now catches and reports failures instead of crashing.
- resources/views/components/errors/_error-page.blade.php - moved from `resources/views/errors/_error-page.blade.php` so `<x-errors._error-page>` resolves (fixes 500-instead-of-403 on error pages).

### Verification
- php artisan test - full suite 134/134 passing (658 assertions), including RequirementTrackerTest (403 error page now renders) and AnnouncementTest.
- vendor/bin/pint - clean.
- npm run build - clean.
- php artisan view:cache - clean.

### Notes
- Existing `data-confirm` modal confirmations (accounts, calendar, announcements, enrollment requests, requirements, submissions, message center, digital IDs) and the submit-button loading state were already in place and confirmed wired on `DOMContentLoaded`.
- Login uses username, not email. Demo passwords: office Office123!, others Demo123!.
