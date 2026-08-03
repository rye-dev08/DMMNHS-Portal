# Code Manner — DMMNHS Student Portal

This file is the authoritative ruleset for working in this repository. Follow it on
every change. It also documents the required verification workflow.

---

## 1. Global Working Rules

- **No Git operations, ever, unless the user explicitly asks.** Never `init`, `add`,
  `commit`, `push`, `pull`, `rebase`, `amend`, `checkout`, open a PR, or create a branch.
- Follow the user's instructions literally. Do not add unrequested work or "scope creep".
- Ask before major architectural / design decisions. Propose options and let the user confirm.
- **Never redesign working functionality without explicit approval.** This project is a
  migration/refactor — preserve existing behavior.
- When you find dead or broken code, **report it** instead of silently changing it.
- Keep `changes.md` updated with a versioned entry for every change.
- Keep `memory.md` updated with the persistent architecture/state.
- Do not expose, log, or commit credentials/secrets. Follow security best practices at all times.

---

## 2. Responding to the User

- Be concise. On the CLI keep answers under ~4 lines unless the user asks for detail.
- Only use emojis if the user explicitly requests them. Never add them to files otherwise.
- After finishing an edit, stop — do not add a summary unless asked.

---

## 3. Code Style

- **Do not add code comments unless the user asks.**
- Mimic the style and patterns already used in the surrounding files.
- Never assume a library/framework/package is available. Verify first in
  `composer.json` and `package.json`.
- Prefer editing existing files over creating new ones. Create files only when required.
- Do not create documentation/README files unless the user explicitly requests them.
- Use OOP and keep concerns separated (thin controllers, models for data,
  services for complex logic).
- Reuse existing helpers, components, and utilities rather than re-implementing.

---

## 4. Stack / Framework Conventions

- **Laravel 13** + **PHP ^8.3**, Blade for views, **Tailwind CSS v4** via
  `@tailwindcss/vite`.
- Build styles with **Tailwind utility classes**. After changing markup/classes, rebuild
  assets with `npx vite build` (output goes to `public/build/`).
- Reusable UI goes in **Blade anonymous components** under `resources/views/components/`
  (e.g. `card`, `notice`, `brand`, `form-input`, `primary-button`).
- Use `@props([...])` and `$attributes->merge([...])` for component customization.
- Flash notices: use the `flash_notice()` helper (in `app/Support/helpers.php`, auto-loaded
  via `composer.json` `autoload.files`). Render notices with `<x-notice />` into an
  `#alert-host` element; toasts are shown by `showNotice()` in `resources/js/app.js`.
- Auth is Laravel built-in + `CheckRole` middleware (alias `role`) for
  `admin` / `teacher` / `student`. Password policy is the shared `PasswordPolicy` rule
  (min 8 chars + uppercase OR symbol). Inactive users cannot log in.
- Database is local **MySQL `student_portal`** (Laragon). `.env` has
  `DB_CONNECTION=mysql`. **Keep `APP_NAME` quoted** in `.env`
  (`APP_NAME="DMMNHS Student Portal"`) — an unquoted value breaks `dump-autoload`.

---

## 5. Conventions to Preserve

- Table and column names exactly as defined in the legacy schema (and in the migrations).
- Roles: `admin`, `teacher`, `student`.
- Status values: `active`, `inactive`.
- Enrollment request statuses: `pending`, `approved`, `rejected`.
- Grade strings: numeric `'0'`–`'100'`, `'N/A'`, `'INC'`, `'DROPPED'`.
- Quarter strings: `'Sem 1'`, `'Sem 2'`, … (derived from `settings.current_semester`).
- Settings singleton: `settings.id = 1`.
- Teacher capacity: `COALESCE(teacher_approval.max_students, teachers.max_students, 30)`.
- Flash notices are the flow messages everywhere (success/error/info).

---

## 6. Verification Workflow

Run these whenever relevant:

- `composer dump-autoload` — after changing composer `autoload.files` or adding helpers.
- `php artisan db:seed` / `db:seed --class=...` — to seed demo or base data.
- `php artisan route:list` — confirm routes.
- `npm install` then `npx vite build` — after any CSS/JS/Tailwind change.
- `php artisan config:clear` / `view:clear` — after `.env` or config changes.
- Smoke-test with `php artisan serve`:
  - Public pages → 200.
  - Guest hitting a role route → 302 (redirect to login).
  - Log in as `admin`/teacher/student; visit role pages → 200.
  - Role mismatch → redirect back to login.
- For POST smoke tests: fetch a page, extract the `_token` (CSRF), log in via POST, then
  GET authenticated pages. Note the CSRF token regenerates after login — fetch a fresh
  token from an authenticated page before POSTing again.
- Test data cleanup: remove any rows created during verification so the DB stays clean.
- Final sanity: `php artisan about` boots cleanly.

Demo accounts (password `Demo123!` for all):
- admin: `admin` / `Admin123!`
- teachers: `maria.santos`, `john.cruz`, `elena.reyes`, `rizalina.bautista`
- students: e.g. `juan.dela.cruz`

---

## 7. Docs Management

- `changes.md` — append a new versioned section per change:
  `## DMMNHS-V.x.y: <short title>` with **Files Changed / Changes / Reason / Verification / Notes**.
- `memory.md` — keep Section 2 (current app state) accurate: framework, DB, migrations,
  models, auth, routes, controllers, views, frontend, seeders, assets.