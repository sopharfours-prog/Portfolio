# Sophar Portfolio — Website + Admin Dashboard

A Laravel 13 application that serves the public portfolio website **and** its admin dashboard.
Everything on the website (hero, about, projects, services, skills, experience, testimonials,
contact details, social links, SEO) is stored in MySQL and edited from `/admin`. No source code changes are needed.

| Area          | URL                                  |
| ------------- | ------------------------------------ |
| Website       | `http://localhost:8000`              |
| Admin         | `http://localhost:8000/admin`        |
| JSON API      | `http://localhost:8000/api/projects` |

## Requirements

- PHP 8.3+ with `pdo_mysql`, `gd` (with WebP), `dom`, `fileinfo`, `mbstring`
- Composer 2
- Node.js 20+
- Docker (for the development MySQL database)

## Installation

```bash
cd portfolio-app

# 1. Dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (MySQL 8.4 in Docker, on port 3307)
docker compose up -d

# 4. Tables + content
php artisan migrate --seed

# 5. Public link for uploaded images
php artisan storage:link

# 6. Front-end assets
npm run build
```

`php artisan migrate --seed` prints the admin accounts it creates. **Copy the passwords: they are shown only once.**

| Role        | Email               | Password                                                    |
| ----------- | ------------------- | ----------------------------------------------------------- |
| Super Admin | `admin@sophar.dev`  | random (printed by the seeder), or `ADMIN_PASSWORD` in `.env` |
| Editor      | `editor@sophar.dev` | random (printed by the seeder)                              |

Set `ADMIN_NAME`, `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env` before seeding to choose your own.
Lost a password? Use "Forgot password?" on the login page. In development the reset link is written to `storage/logs/laravel.log` (`MAIL_MAILER=log`).

## Run (development)

```bash
docker compose up -d      # database
composer serve            # http://localhost:8000 (php artisan serve with 12 MB uploads, see docker/php/uploads.ini)
npm run dev               # optional: hot reload while editing CSS/JS
```

If Docker was restarted, run `docker compose up -d` again before `php artisan serve`.

## Tests

```bash
php artisan test
```

32 feature tests (392 assertions) cover login/logout, rate limiting, CSRF, password reset, roles,
CRUD for every content type, uploads and SVG sanitising, messages, notifications, the activity log,
settings, both languages, the public site and the API.
Tests use a separate `portfolio_test` database, created automatically by `docker/mysql/init.sql`
the first time the MySQL volume is created.

## Build for production

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Production `.env` checklist:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`
- `DB_*` for your MySQL server (the migrations are standard Laravel; nothing is Docker-specific)
- `SESSION_SECURE_COOKIE=true` (HTTPS only)
- A real `MAIL_MAILER` (SMTP, etc.) so password-reset emails are delivered
- Serve through nginx or Apache with gzip/brotli and long cache headers for `/build/*` (the file names are hashed)
- Do **not** run `db:seed` on a live site: it inserts the sample content

## Sign in with Google / Apple (visitor accounts)

Visitors can log in with email + password, Google or Apple. Administrators always use email + password.
A button only appears on `/login` and `/register` once its keys are in `.env`; then run `php artisan config:clear`.

**Google** — [Google Cloud Console](https://console.cloud.google.com/apis/credentials) → Create credentials → OAuth client ID → *Web application*.
Authorised redirect URI: `https://your-domain/auth/google/callback` (locally `http://localhost:8000/auth/google/callback`).

```dotenv
GOOGLE_CLIENT_ID=...apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=...
```

**Apple** — needs a paid Apple Developer account and HTTPS (Apple does not accept `localhost`).
In [Certificates, Identifiers & Profiles](https://developer.apple.com/account/resources): create an App ID with *Sign in with Apple*,
a **Services ID** (domain `your-domain`, return URL `https://your-domain/auth/apple/callback`) and a **Key** with *Sign in with Apple* (download the `.p8` file once).

```dotenv
APPLE_CLIENT_ID=dev.sophar.signin        # the Services ID
APPLE_TEAM_ID=ABCDE12345
APPLE_KEY_ID=XYZ987WVU6
APPLE_PRIVATE_KEY=/absolute/path/to/AuthKey_XYZ987WVU6.p8   # keep outside public/
```

An existing visitor account with the same email is linked rather than duplicated. Apple's callback is a cross-site POST, so it is
excluded from CSRF and verified by an encrypted single-use nonce cookie instead (`SocialLoginController`).

## What the admin can do

| Section            | Path                               | Who         | Notes |
| ------------------ | ---------------------------------- | ----------- | ----- |
| Dashboard          | `/admin`                           | all         | Stat cards, visitors/page views chart (7 days, 30 days, 90 days, 1 year), recent projects and messages |
| Projects           | `/admin/projects`                  | all         | Search, status tabs, category filter, sort, pagination; view, edit, duplicate, delete |
| Project editor     | `/admin/projects/create`           | all         | Lao/English fields, drag-and-drop gallery (upload, reorder, set cover, remove), save draft / publish |
| Services           | `/admin/services`                  | all         | Bilingual, icon picker, drag to reorder |
| Skills             | `/admin/skills`                    | all         | Category, level, icon, drag to reorder |
| Experience         | `/admin/experience`                | all         | Timeline entries, current position, drag to reorder |
| Education          | `/admin/education`                 | all         | Degrees and certificates, drag to reorder |
| Testimonials       | `/admin/testimonials`              | all         | Photo, rating, Published / Draft / Hidden |
| Messages           | `/admin/messages`                  | all         | Inbox, Unread, Read, Archived, Trash; search; reply opens your email app |
| Media Library      | `/admin/media`                     | all         | Upload, search, filter, preview, rename, alt text, copy URL, delete |
| Website → Hero     | `/admin/website/hero`              | Super Admin | Greeting, name, job title, description, image, two buttons |
| Website → About    | `/admin/website/about`             | Super Admin | About page heading (with highlighted word), subheading, button text and URL, profile image, introduction, biography, statistics, social links, live preview |
| Website → Contact  | `/admin/website/contact`           | Super Admin | Email, phone, address; each social platform has an on/off switch |
| Settings           | `/admin/settings`                  | Super Admin | General, SEO, Social, Appearance, Language |
| Users              | `/admin/users`                     | Super Admin | Create Super Admins and Editors |
| Activity Log       | `/admin/activity`                  | Super Admin | Who did what, when, from which IP |
| Profile            | `/admin/profile`                   | all         | Name, email, photo, password |

Also: notification dropdown (mark as read, mark all, clear), LA | EN switch, Light / Dark / System theme
(saved in `localStorage`), confirmation dialog before every delete, toasts that disappear after 4 seconds.

**Roles.** Editors manage projects, services, skills, experience, testimonials, messages and media.
Website content, settings, users and the activity log are Super Admin only (enforced by the `super-admin` gate on the routes, not just hidden in the menu).

## How the website gets its content

- Pages are rendered by Laravel (Blade) from the database, so an edit in the admin is live on the next page load and fully visible to search engines.
- The visitor's language (LA | EN) is chosen with `/lang/lo` or `/lang/en` and remembered in the session. The default and enabled languages are set in **Settings → Language**.
- Fixed interface labels (menu names, button text, form messages) are in `lang/lo/site.php` and `lang/en/site.php`.
- Admin interface: English strings in the views, Lao in `lang/lo.json`.
- The contact form saves to the `messages` table and notifies every admin.
- Page views are recorded first-party in `page_views` (path + a daily salted hash, never the raw IP). Bots and signed-in admins are not counted. The seeded analytics history is demo data.

## Project structure

```text
app/
  Http/Controllers/Admin/    admin pages (one controller per section)
  Http/Controllers/Api/      JSON API
  Http/Controllers/Site/     public website + contact form
  Http/Requests/             validation shared by admin forms and the API
  Http/Middleware/           SetLocale, TrackPageView, SecurityHeaders, ForceJsonResponse
  Models/                    Project, Service, Skill, Experience, Testimonial, Message, Media, Setting, ...
  Support/                   ImageUploader, SvgSanitizer, Listing (search/filter/sort/paginate), Analytics
database/
  migrations/                schema
  seeders/                   sample content (data/portfolio.json + images/)
resources/
  admin/{css,js}/            admin design system and behaviour
  site/{css,js}/             public website styles and behaviour
  views/admin/               admin Blade views
  views/site/                website Blade views
  views/components/admin/    reusable form components (input, bilingual, image-input, icon-input, badge, ...)
  icons/                     Bootstrap Icons (SVG), rendered inline by the icon() helper
lang/                        translations
routes/web.php               website + admin routes
routes/api.php               API routes
tests/Feature/               automated tests
docker-compose.yml           development MySQL
```

## Database

| Table                 | Purpose |
| --------------------- | ------- |
| `users`               | Admin accounts (`role`: `super_admin` or `editor`) |
| `projects`            | Portfolio projects (bilingual text as JSON `{"lo": "...", "en": "..."}`) |
| `project_images`      | Gallery images of a project (`is_cover`, `sort_order`) → `projects` |
| `services`, `skills`, `experiences`, `testimonials` | Ordered content lists |
| `messages`            | Contact form submissions (`folder`, `read_at`) |
| `media`               | Media Library files |
| `settings`            | Key → JSON: `general`, `seo`, `hero`, `about`, `contact`, `appearance`, `language` |
| `social_links`        | One row per platform, with `enabled` |
| `admin_notifications` | Per-user notifications → `users` |
| `activity_logs`       | Audit trail → `users` |
| `page_views`          | Website analytics |

`admin_notifications` is named that way because Laravel reserves `notifications` for its own notification system.

## API

Reads of **published** content are public. Everything else needs authentication:
the admin session cookie (same browser) or a Sanctum token (`Authorization: Bearer <token>`).

| Method             | Endpoint                         | Auth        |
| ------------------ | -------------------------------- | ----------- |
| GET                | `/api/projects`, `/api/projects/{slug}` | public (published only) |
| GET                | `/api/services`, `/api/skills`, `/api/experiences`, `/api/testimonials` | public |
| GET                | `/api/settings`                  | public (safe keys only) |
| POST               | `/api/messages`                  | public, rate limited (contact form) |
| POST, PUT, PATCH, DELETE | `/api/{projects,services,skills,experiences,testimonials}` | signed in |
| GET, PATCH, DELETE | `/api/messages`                  | signed in |
| GET, POST, PATCH, DELETE | `/api/media`               | signed in |
| PUT                | `/api/settings/{key}`            | Super Admin |

List endpoints accept `search`, `status`, `sort` (`order`, `newest`, `oldest`), `page` and `per_page`.
`PATCH` changes `status` and/or `sort_order` only; `PUT` replaces the whole item.

Create a token:

```bash
php artisan tinker --execute 'echo App\Models\User::first()->createToken("cli")->plainTextToken;'
```

## Security

- Passwords are hashed (bcrypt, via Laravel's `hashed` cast); minimum 10 characters with upper case, lower case and a number.
- Session authentication, session ID regenerated on login, CSRF token on every form and AJAX request.
- Rate limits: login and password reset 5 per minute per email + IP; contact form 5 per 10 minutes per IP; API 120 per minute.
- Uploads: `jpg`, `jpeg`, `png`, `webp`, `svg` only; extension **and** MIME type are checked; 5 MB limit;
  JPG/PNG are re-encoded to WebP (max 2400px), which also strips metadata; SVGs are sanitised (scripts, event handlers and external references removed).
- All output is escaped by Blade; bilingual text and lists are stored as JSON, never as HTML.
- Security headers on every page (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS on HTTPS); admin pages are sent with `Cache-Control: no-store`.
- Login failures, logins, logouts and every content change are written to the activity log.

## Known limits

- Form validation messages in the admin are in English even when the admin language is Lao (Laravel's built-in messages have no Lao translation; add `lang/lo/validation.php` to translate them).
- "Reply" on a message opens your email app; the dashboard does not send email itself.
- The admin theme is stored per browser, not per account.
