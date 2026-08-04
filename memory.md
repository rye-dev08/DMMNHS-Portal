# Project Memory — DMMNHS Student Portal

This file is the persistent documentation for the DMMNHS Student Portal project. It is meant to help future development sessions understand the project, its current state, and the migration plan.

---

## 1. Project Overview

- **Application:** Student Portal for **Don Mariano Marcos National High School (DMMNHS)**.
- **Goal:** Migrate/refactor the existing plain-PHP project (in `dmmnhs/`) into a **Laravel** application.
- **Frontend target:** Tailwind CSS.
- **Approach:** Preserve existing functionality; this is a migration/refactoring, not a redesign.
- **Repository layout (current):**
  - Root `C:\laragon\www\student-portal\` — a freshly installed **Laravel 13** application (empty starter).
  - `C:\laragon\www\student-portal\dmmnhs\` — the **existing legacy PHP project** to be migrated.

---

## 2. Current State of the Laravel App (root) — AFTER MIGRATION (DMMNHS-V.1.1→V.2.5.2)

Laravel **13** app (`laravel/framework ^13.8`, PHP ^8.3), migrated from the legacy `dmmnhs/` PHP project.

- **Database:** local MySQL **`student_portal`** (Laragon, `127.0.0.1:3306`, user `root`, empty password, `utf8mb4`). `.env`: `DB_CONNECTION=mysql`. **NOTE:** `APP_NAME` must stay quoted in `.env` (`APP_NAME="DMMNHS Student Portal"`).
- **Migrations:** all legacy tables migrated with identical names/columns plus an added `teacher_subjects` table (legacy created it implicitly), and `contact_messages` (new, for the wired contact form). Indexes/unique constraints preserved (e.g. unique `teacher_id+subject_name` on `teacher_subjects`, unique `(student_id, subject_id, quarter)` on `grades`, unique `(teacher_id, student_id, score_type, item_no)` on `assessment_scores`). Settings singleton `settings.id=1` seeded with `current_semester=1`, `school_year='2025-2026'`.
- **Models:** `User` (custom `getAuthPassword()` for `password_hash` column + legacy plaintext fallback & auto-rehash, `isAdmin/isTeacher/isStudent`), `Student`, `Teacher`, `TeacherApproval`, `EnrollmentRequest`, `Subject`, `Grade`, `AssessmentScore`, `Setting` (`incrementing=false`), `PreviousSemesterSubject`, `PreviousSemesterGrade`, `GraduatedStudent`, `TeacherSubject`, `ContactMessage`.
- **Auth:** Laravel built-in auth (`web` guard) + `CheckRole` middleware (alias `role`) for `admin`/`teacher`/`student`. Login redirects by role; inactive users rejected; CSRF enabled; `PasswordPolicy` rule (min 8 chars + uppercase OR symbol); single shared change-password page.
- **Routing (`routes/web.php`):** public `/`, `/about`, `/contact` (+ `POST /contact` submit); `POST /login`, `POST /logout`; auth group with `admin.*`, `teacher.*`, `student.*` route groups + `/change-password` + `/teacher/subjects` JSON endpoint.
- **Controllers:** `Auth\AuthController`, `PasswordController`, `PageController` (about/contact/submitContact), `Admin\*` (Dashboard, Account, EnrollmentSetting), `Teacher\*` (Dashboard, Subject, EnrollmentRequest, Grade, GradesOverview, Info), `Student\*` (Dashboard, StudentInfo, Schedule, Grade, Enrollment). **Self-service info edit (V.2.1):** `StudentInfoController@edit/update` lets students change name/email/sex/birthday/age; `InfoController@edit/update` (teacher) lets teachers change name/email. Username is read-only (login identifier); admin-owned fields (grade level, status, advisory, limits) are excluded from self-service forms.
- **Views/Blade:** layouts `layouts/app|guest|header|footer|sidebar`, components `notice`, `card`, `brand`, `decorative-background`, `login-card`, `form-input`, `password-input`, `primary-button`, `google-button`; all role pages ported as Blade views under `resources/views/{admin,teacher,student}`. **Unified role-aware layout (V.1.6):** `<x-layouts.app />` composes a persistent futuristic **sidebar** (`layouts/sidebar.blade.php`) with a slim top **header** and matching **footer** — all roles inherit the same shell. Sidebar is fixed on `lg+` and an off-canvas drawer on mobile. **Futuristic content panels (V.1.7):** the shared `<main>` panel is a layered futuristic surface (gradient border + glow, grid backdrop, corner brackets, top status line, radial glows) and all admin pages use dark-navy gradient table headers, zebra rows, blue-accent inputs, gradient buttons, and step-numbered account forms. **No yellow boxes (V.1.8):** all remaining yellow-tinted surfaces (`#e6c84b`, `rgba(255,246,179,…)`) were replaced — the shared `card` component, student/teacher info sections, and the main panel now use the blue/cyan futuristic style. **Notifications & validation (V.2.0):** `resources/js/app.js` ships a global notification system — top-right sliding toasts (`showToast`, incl. the backward-compat `showNotice`/`floatingAlert`), a centered success modal (`showModal`), and a generic `form[data-validate]` custom validator (`novalidate` + inline red-border/shake `.field-error`) replacing native browser bubbles. `app/Support/helpers.php` adds `flash_modal()`; `<x-notice />` renders both `flash_notice` (toast) and `flash_modal` (modal). Password change success uses the centered "Password Updated" modal; login + change-password forms use the custom validator (`data-match`, `data-password-policy`, `data-min`). Styling lives in `resources/css/app.css` (`.toast*`, `.modal*`, `.field-error`).
- **Frontend:** Tailwind CSS **v4** via `@tailwindcss/vite`. `resources/css/app.css` + ported `resources/js/app.js` (menu/sidebar toggle, floating alerts, password validation, password eye-toggle). Logo `public/images/dmnhs-no-bg.jpg`. Production build in `public/build/` (via `npx vite build`). A building photo at `public/images/campus.jpg` is auto-included in the login branding panel if present.
- **Helper:** `flash_notice()` in `app/Support/helpers.php`, auto-loaded via `composer.json` `autoload.files`.
- **Seeders:** `SettingSeeder`, `DatabaseSeeder` (admin `admin`/`Admin123!`), `DemoSeeder` (demo data, all accounts password `Demo123!`).
- **Demo data:** 4 teachers (`maria.santos`, `john.cruz`, `elena.reyes`, `rizalina.bautista` — advisory 7-A/B/C, 8-A), 11 students (e.g. `juan.dela.cruz`), 5 subjects each, approved enrollments, Sem 1 grades. All seeded accounts login with `Demo123!`.
- **Legacy credentials:** `dmmnhs/includes/config.php` neutralized (InfinityFree credentials removed, local placeholders only).
- **Docs:** `memory.md` (this file), `changes.md` (versioned change log, `DMMNHS-V.x.y`), and `codemanner.md` (authoritative coding rules & verification workflow for this repo).
- **Admin accounts (V.1.5):** split into **Create Account** (`admin/accounts/create`) and **Manage Accounts** (`admin/accounts`) pages. Manage has role-filter tabs (all/student/teacher/admin), free-text search, pagination (15/page), inline activate/deactivate toggle, edit (role-specific profile fields), reset-password form, and cascade delete. Guards: cannot deactivate/delete self or the last active admin. `store` creates student profiles (active) or teacher profiles (active + approved, V.2.5); role rule accepts `admin,teacher,student`.
- **Teacher accounts auto-approved (V.2.5):** the old **Approve Teachers** step is removed. `AccountController@store` now creates teacher accounts as **active** immediately, sets advisory/max-student/max-subject capacity (from the Create Account "Teacher Profile" fields, falling back to enrollment-settings defaults), and writes an approved `teacher_approval` row so the existing capacity checks (`COALESCE(ta.max_students, t.max_students, 30)`) keep working. `TeacherApprovalController`, `ApproveTeacherRequest`, the `approve_teachers` view/route, and its sidebar/dashboard links were removed; the admin dashboard "Pending Teachers" card was dropped (replaced by a Teacher Advisory quick action).
- **Audit fixes (V.2.4):** all §6 audit items resolved — grade submission ownership checks, `/teacher/subjects` JSON role-gated (`auth` + `role:teacher`), login/contact throttling (named limiters `login` 5/min, `contact` 3/min in `AppServiceProvider`), `graduated_students` written on end-of-school-year, subject duplicate check skips empty `course_code`, subject-delete grade-block keyed by `subject_name`, teacher dashboard null-guard, GradesOverview term filter. See Section 6.
- **Per-student subjects (V.2.2):** `subjects` rows are **one-per-student-per-subject** (`student_id`+`teacher_id`). Grade listing must always filter by `student_id`, never by `teacher_id` alone (that duplicates/mixes other students' rows). Fixed in `Student\GradeController`, `Teacher\GradeController::getSubjects`, and rewritten `Teacher\GradesOverviewController` (matrix keys grades by `subject_name` now). Grades have a unique `(student_id, subject_id, quarter)` constraint.
- **Collapsible sidebar (V.2.5.2):** the fixed `w-[264px]` desktop sidebar can be hidden via the new header button `#sidebar-collapse-toggle` (`hidden lg:inline-flex`). The handler `initSidebarCollapse()` in `resources/js/app.js` toggles the `sidebar-collapsed` class on `<body>` and persists it in `localStorage` (`sidebar-collapsed` = `1`) so the choice survives reloads. Styling is a plain CSS media query in `resources/css/app.css` (matching Tailwind v4, which uses the `translate` property rather than `transform`): `body.sidebar-collapsed #app-sidebar { translate: -100% 0 }` and `body.sidebar-collapsed #app-shell { padding-left: 0 }`. The content shell `#app-shell` in `layouts/app.blade.php` keeps `transition-[padding]` (300ms) so collapse/expand animates. Mobile drawer behavior is unchanged.

---

## 3. Existing Legacy Project (`dmmnhs/`)

### 3.1 Folder Structure

```
dmmnhs/
├── index.php                 # Login page (custom session auth, PRG pattern)
├── about.php                 # Public About Us page
├── contact.php               # Public Contact Us page (form is template-only)
├── account.php               # Dev helper: inserts a hardcoded admin account (ad-hoc, not part of normal flow)
├── get_subjects.php          # JSON endpoint for subject lists (used by teacher submit_grades)
├── logout.php                # Destroys session, redirects to index.php
├── database_fixed.sql        # Schema for all app tables (MySQL)
├── admin/                    # ADMIN role pages
│   ├── dashboard.php
│   ├── accounts.php          # Create/delete users (student/teacher profiles)
│   ├── approve_teachers.php  # Approve pending teachers, set limits
│   ├── enrollment_settings.php # Semester / school-year management + advisory class
│   └── change_password.php
├── teacher/                  # TEACHER role pages
│   ├── dashboard.php
│   ├── advisory_portal.php   # Add/delete class subjects, auto-apply to students
│   ├── enrollment_requests.php # Approve/reject student requests (class capacity)
│   ├── grades_overview.php   # Grade matrix (students x subjects)
│   ├── submit_grades.php     # Save grades (upsert per student+subject+semester)
│   ├── info.php              # Teacher profile info
│   └── change_password.php
├── student/                  # STUDENT role pages
│   ├── dashboard.php
│   ├── student_info.php      # Profile + adviser info
│   ├── class_schedule.php    # Current semester schedule
│   ├── grades.php            # Grades with color coding + GWA
│   ├── enrollment_request.php # Send enrollment request to a teacher
│   └── change_password.php
├── includes/
│   ├── config.php            # DB credentials (InfinityFree host) — contains credentials
│   ├── db.php                # mysqli connection bootstrap
│   ├── functions.php         # Auth helpers, flash notices, grade mapping, password rules
│   ├── layout_start.php      # Shared header/nav layout (role-based menu)
│   ├── layout_end.php        # Shared footer layout
│   └── dmnhs-no-bg-copy.jpg  # (image file)
└── assets/
    └── cs/
        ├── style.css         # Full custom CSS (~530 lines)
        ├── dmnhs-no-bg.jpg   # School logo
        └── js/
            └── main.js       # Menu toggle, floating alerts, password validation
```

### 3.2 Authentication (legacy)

- **Custom session-based** auth. No framework, no library.
- Login flow in `index.php`:
  - Reads `username`/`password` POST.
  - Looks up `users` by `username`.
  - Verifies `password_hash` with `password_verify()`; supports a legacy **plaintext fallback** that auto-upgrades to `password_hash(..., PASSWORD_DEFAULT)`.
  - Redirects by role: `admin` → `admin/dashboard.php`, `teacher` → `teacher/dashboard.php`, else `student/dashboard.php`.
  - Uses PRG (Post/Redirect/Get) with a `flash_notice` stored in session.
- Guards:
  - `check_login()` — ensures a session exists and the user is active.
  - `check_role($role)` — ensures the correct role session is active (supports multi-tab/multi-role via `$_SESSION['auth'][$role]`).
- Password policy (`validate_password`): min 8 chars, must contain uppercase OR symbol.
- `logout.php` — `session_unset()` + `session_destroy()`.

### 3.3 Database (legacy) — MySQL

Connection: **InfinityFree** shared MySQL host (see `includes/config.php`). Schema defined in `database_fixed.sql`:

| Table | Purpose / Notable Columns |
|---|---|
| `users` | `id, name, username (unique), email, password_hash, role ENUM('admin','teacher','student'), status ENUM('active','inactive')` |
| `students` | `id, user_id (unique), sex, birthday, age, grade_level, status, needs_reenrollment` |
| `teachers` | `id, user_id (unique), advisory_class, max_subjects, max_students, status` |
| `teacher_approval` | `id, teacher_id, max_students, max_subjects, status` |
| `enrollment_requests` | `id, student_id, teacher_id, status ENUM('pending','approved','rejected'), date_requested` |
| `subjects` | `id, teacher_id, student_id, subject_name, course_code, teacher_code, room_no, created_at` (enrollment rows per student) |
| `grades` | `id, student_id, subject_id, grade VARCHAR(10) DEFAULT 'N/A', remarks, quarter, date_submitted` — has UNIQUE `(student_id, subject_id, quarter)` |
| `assessment_scores` | `id, teacher_id, student_id, score_type ENUM('activity','quiz','exam'), item_no, score, max_score, remarks, created_at, updated_at` — created at runtime by `ensure_assessment_scores_table()` |
| `settings` | singleton row `id=1`: `current_semester, current_school_year, max_students_per_class, max_subjects_per_teacher` |
| `previous_semester_subjects` | archived subjects at semester end |
| `previous_semester_grades` | archived grades at semester end |
| `graduated_students` | students who graduated |
| `teacher_subjects` | teacher-defined subjects (applied to all approved students) |

**Notes:**
- `grades.grade` is a **VARCHAR**, values like `'85'`, `'N/A'`, `'INC'`, `'DROPPED'`.
- `quarter` stores values like `'Sem 1'`, `'Sem 2'`.
- The `assessment_scores` table is created on the fly by `ensure_assessment_scores_table()` (called in `admin/accounts.php`), not in `database_fixed.sql`.
- `settings` uses a fixed `id=1` singleton pattern.

### 3.4 Roles & Functionality (legacy)

**Admin**
- Dashboard (placeholder welcome).
- **Manage Accounts** (`accounts.php`): create users (teacher/student) with password validation; creates `students`/`teachers` profile rows; delete user with cascade cleanup across grades, subjects, enrollment_requests, assessment_scores, teacher_approval; lists users grouped by role (admins, students w/ year+section, teachers w/ advisory).
- **Approve Teachers** (`approve_teachers.php`): approve inactive teachers, set `max_students`, `max_subjects`, `advisory_class`; syncs `teachers` + `teacher_approval`; activates `users.status`.
- **Enrollment Settings** (`enrollment_settings.php`):
  - `save_advisory` — set a teacher's advisory class.
  - `end_semester` — archives subjects/grades to `previous_semester_*`, clears enrollment_requests/grades/subjects/teacher_subjects, resets teachers' advisory, marks students `needs_reenrollment='yes'`, increments semester.
  - `end_school_year` — archives, increments grade levels (deletes students reaching grade ≥ 14), resets to semester 1 and next school year.
- **Change Password** — verify old, validate new, update hash.

**Teacher**
- Dashboard: welcome, privacy notice, advisory label, subject-entry count, approved-student count.
- **Advisory Portal** (`advisory_portal.php`): add subject to `teacher_subjects` (duplicate check on name OR course code), auto-apply to all approved students in `subjects`; delete subject (blocked if any student has grades for it).
- **Enrollment Requests** (`enrollment_requests.php`): list pending requests; **approve** (enforces teacher capacity from `teacher_approval.max_students`, then auto-creates subject rows for the student from `teacher_subjects`); **reject**.
- **Submit Grades** (`submit_grades.php`): pick an approved student, pick a subject (via `get_subjects.php` JSON), enter grade (0–100 or N/A) + remarks; upserts into `grades` keyed by `(student_id, subject_id, quarter='Sem N')`.
- **Grades Overview** (`grades_overview.php`): matrix of students × subjects showing latest grade with color coding (`map_grade_display`).
- **Teacher Info** (`info.php`): profile + limits display.
- **Change Password.**

**Student**
- Dashboard: welcome, privacy notice, info cards.
- **Student Info** (`student_info.php`): profile fields + adviser/advisory/max subjects from approved enrollment request.
- **Class Schedule** (`class_schedule.php`): subjects for the student in current semester w/ teacher name.
- **Grades** (`grades.php`): current-semester grades per subject with color coding + computed **GWA** (ignores non-numeric grades).
- **Enrollment Request** (`enrollment_request.php`): guard against graduated/inactive; duplicate-request guard; teacher capacity check; insert pending request; list own requests.
- **Change Password.**

**Public/other**
- `about.php`, `contact.php` — public pages (contact form is a template only, not wired to a backend).
- `get_subjects.php` — JSON API used by teacher `submit_grades.php` (`?student_id=..&teacher_id=..`).
- `account.php` — an ad-hoc dev script that inserts an admin account (hardcoded credentials). Not part of the normal app flow.

### 3.5 Frontend (legacy)

- **Styling:** single static stylesheet `assets/cs/style.css` (~530 lines). Key design tokens:
  - Background `#f5f7fb`; panel yellow-tint `rgba(255,243,153,0.42)`; primary `#0018f9` / dark `#0080fe`; accent `#9a3412`.
  - Classes: `site-header`, `brand-wrap`, `header-nav-panel`, `page-shell`, `card`, `login-box`, `auth-form`, `stack-form`, `field-row`, `inline-actions`, `btn-link`, `btn-delete`, `profile-card`, `profile-grid`, `profile-item`, `privacy-note`, `fb-alert-*`, `alert-host`, `subtle`, etc.
- **JS:** single file `assets/cs/js/main.js`:
  - `initHeaderMenu()` — mobile menu toggle (hamburger).
  - `showNotice(msg, type)` / `floatingAlert()` — floating alert toasts (FB-style) rendered into `#alert-host`.
  - `window.alert` override → toast.
  - `checkPassword(inputId)` — client-side password validation.
  - `updateGrade()` — sample AJAX helper (references `update_grade.php`, which does **not** exist — dead code).
- **Layout:** `layout_start.php` renders the HTML head, header with role-based menu (`admin`/`teacher`/`student` menus), and page shell; `layout_end.php` closes shell + footer.
- **Dependencies:** none (no jQuery, no build tooling). Pure HTML/CSS/JS + mysqli PHP.

---

## 4. Migration Considerations

Things that will matter when migrating to Laravel:

1. **Database engine change:** legacy uses MySQL (InfinityFree); Laravel app currently uses SQLite. Decide the target DB (local MySQL via Laragon vs. keep SQLite) and preserve the existing schema/relationships. **Do not rename tables/columns.**
2. **Auth:** custom session auth must be replaced with a Laravel approach (either the built-in auth with a custom guard/driver, or a Laravel-native implementation preserving role behavior). Roles: `admin`, `teacher`, `student`; user `status` gate (`active`/`inactive`) must be preserved.
3. **Grade display logic:** `map_grade_display()` (N/A, INC, DROPPED, numeric thresholds 83/75) must be preserved exactly.
4. **Password policy:** min 8 chars + uppercase OR symbol (client + server side).
5. **`quarter` convention:** `'Sem ' . current_semester` from `settings` — drives grades upsert and filtering.
6. **Semester/school-year reset flows:** archive → clear → reset logic in `enrollment_settings.php` must be replicated faithfully (probably a Service class).
7. **`assessment_scores` table:** created at runtime in legacy; should become a proper migration.
8. **Teacher capacity logic:** `COALESCE(teacher_approval.max_students, teachers.max_students, 30)` used on both approve-request and student-enroll paths.
9. **Reusable UI:** header/nav/footer layout, cards, tables, buttons, alerts, profile cards are repeated → candidate Blade layouts/components.
10. **Repeated code:** 3 near-identical `change_password.php` (admin/teacher/student) → single reusable page/controller with role middleware.
11. **Public pages + JSON endpoint:** `about`, `contact`, and `get_subjects` JSON endpoint need Laravel routes.
12. **Assets:** logo `dmnhs-no-bg.jpg`, favicon usage, and legacy `style.css` need to be re-homed; Tailwind v4 is already configured.
13. **Credentials:** `includes/config.php` contains live InfinityFree DB credentials — should not be committed/exposed; replace with Laravel `.env` config.
14. **Dead/broken code observed:** `account.php` (ad-hoc account creation with hardcoded creds), `update_grade.php` reference in `main.js` (file does not exist), `contact.php` form is not wired to a backend. Report, do not silently change.

---

## 5. Conventions to Preserve

- Table and column names exactly as in `database_fixed.sql`.
- Role names: `admin`, `teacher`, `student`.
- Status values: `active`, `inactive`.
- Enrollment request statuses: `pending`, `approved`, `rejected`.
- Grade strings: numeric `'0'`–`'100'`, `'N/A'`, `'INC'`, `'DROPPED'`.
- Quarter strings: `'Sem 1'`, `'Sem 2'`, …
- Settings singleton: `settings.id = 1`.
- Flash messages: success/error/info notices rendered as floating alerts.

---

## 6. Pending Audit Fixes (identified in audit, approved to fix)

**Resolved (V.2.4):**

1. **Subject-logic bugs:** fixed (V.2.2) — `subjects` is per-student; all grade/subject listings filter by `student_id` (Student/Grade, Teacher getSubjects, GradesOverview matrix keys by `subject_name`).
2. **GradeController authorization:** `Teacher/GradeController::store` verifies the subject belongs to the logged-in teacher and that student; `getSubjects` derives the teacher id from auth and rejects non-approved students (403).
3. **`/teacher/subjects` JSON role-gating:** moved into the `role:teacher` route group (runs under `auth` + `role:teacher`).
4. **Throttling:** named limiters `login` (5/min) and `contact` (3/min) registered in `AppServiceProvider`; applied to POST `/login` and POST `/contact`.
5. **Admin self-delete / last-admin guard:** implemented in V.1.5 for both deactivate (`toggleStatus`) and delete (`destroy`); no change needed.
6. **`graduated_students` never written:** `endSchoolYear()` now inserts graduating students before deleting their rows.
7. **Subject duplicate check:** only matches `course_code` when non-empty.
8. **Subject delete grade-block:** compares the teacher's subject by `subject_name` (fixes wrong `teacher_subjects.id` vs `subjects.id`).
9. **Teacher dashboard null-deref:** `$teacher` guarded before reading `advisory_class`.
10. **GradesOverview ignores quarter/term:** matrix now filters grades by the current term quarter.
