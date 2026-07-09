# SoilNWater — Development Setup

Laravel 12 application with Vite, MySQL, Laravel Reverb (websockets), and a database-backed queue.

## Prerequisites

| Tool | Version | Notes |
|------|---------|-------|
| PHP | 8.2+ | XAMPP PHP with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd` |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org) |
| Node.js | 20+ | [nodejs.org](https://nodejs.org) |
| MySQL | 8.x | Via XAMPP |
| XAMPP | Latest | Apache + MySQL for `htdocs` hosting |

## Quick Start (Windows / XAMPP)

From the project root (`e:\xampp\htdocs\soilNwater`):

```powershell
.\scripts\setup-dev.ps1
```

This script will:

1. Run `composer install`
2. Copy `.env.example` → `.env` (if missing) and generate `APP_KEY`
3. Create the `snw_db` MySQL database
4. Run migrations
5. Create the `public/storage` symlink
6. Run `npm install` and `npm run build`

Then start XAMPP **Apache** and **MySQL**, and open:

**http://localhost/soilNwater/public**

The health endpoint should return `200 OK`:

**http://localhost/soilNwater/public/up**

## Manual Setup

If you prefer to run steps individually:

```powershell
composer install
copy .env.example .env        # skip if .env already exists
php artisan key:generate

# Create database snw_db in phpMyAdmin, then:
php artisan migrate
php artisan storage:link

npm install
npm run build
```

### Seed sample data (optional)

```powershell
php artisan db:seed
```

Creates permissions, ad templates, community posts, and a test user (`test@example.com`).

## Running the App

### Option A — XAMPP Apache (recommended for this workspace)

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Visit **http://localhost/soilNwater/public**

A root `index.php` forwards requests to `public/index.php` if you use **http://localhost/soilNwater/** instead.

### Option B — Laravel dev server (hot reload)

```powershell
.\scripts\dev.ps1
```

This runs concurrently:

| Service | URL / Port |
|---------|------------|
| Laravel app | http://127.0.0.1:8000 |
| Vite (HMR) | http://localhost:5173 |
| Queue worker | database driver |
| Log tail (Pail) | terminal output |
| Reverb (websockets) | ws://localhost:8080 |

When using `artisan serve`, set `APP_URL=http://127.0.0.1:8000` in `.env`.

## Environment Variables

Copy `.env.example` to `.env` and fill in values as needed:

| Variable | Purpose |
|----------|---------|
| `DB_*` | MySQL connection (default: `snw_db` / `root` / no password) |
| `APP_URL` | Base URL — `http://localhost/soilNwater/public` for XAMPP |
| `GOOGLE_*` | OAuth login and Maps API |
| `MESSAGE_*` | SMS gateway |
| `REVERB_*` | Real-time broadcasting |
| `MAIL_MAILER` | Use `log` locally (emails written to `storage/logs`) |

## Verifying the Environment

After setup, confirm these checks pass:

1. **Dependencies** — `vendor/` and `node_modules/` directories exist.
2. **Config** — `.env` has a non-empty `APP_KEY`.
3. **Database** — `php artisan migrate:status` shows all migrations ran.
4. **Assets** — `public/build/` contains compiled Vite output.
5. **Web** — Homepage loads at the configured `APP_URL`.
6. **Health** — `/up` returns HTTP 200.
7. **Storage** — `public/storage` symlink exists.

## Common Issues

### `SQLSTATE[HY000] [1049] Unknown database 'snw_db'`

Create the database in phpMyAdmin or run:

```sql
CREATE DATABASE snw_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Vite manifest not found

```powershell
npm run build
```

Or run `.\scripts\dev.ps1` during development for the Vite dev server.

### Permission / storage errors

```powershell
php artisan storage:link
```

Ensure `storage/` and `bootstrap/cache/` are writable by the web server.

### Google OAuth redirect mismatch

Set `GOOGLE_REDIRECT_URI` in `.env` to match your local URL:

```
GOOGLE_REDIRECT_URI=http://localhost/soilNwater/public/auth/google/callback
```

## Running Tests

```powershell
composer test
```

Tests use an in-memory SQLite database (configured in `phpunit.xml`).

## Project Structure

```
app/            Application code (controllers, models, services)
config/         Laravel configuration
database/       Migrations and seeders
public/         Web root (index.php, uploads, built assets)
resources/      Blade views, Sass, JavaScript
routes/         Web, console, and channel routes
scripts/        Development setup and run scripts
storage/        Logs, cache, sessions, file uploads
tests/          PHPUnit feature and unit tests
```
