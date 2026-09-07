# Installation Guide – District Education Resource Allocation System (DERAS)

This guide explains how to set up and run the DERAS project locally.
Recommended environment: Laragon on Windows.

DERAS is a Laravel 12 (PHP) web application with MySQL/MariaDB and Vite front-end assets.

## 1. Prerequisites

Make sure the following are installed:

| Software | Notes |
| --- | --- |
| Laragon (Full) | Includes PHP, MySQL/MariaDB, Apache/Nginx, Composer |
| PHP | 8.2 or newer (php -v) |
| Composer | composer -V |
| Node.js | 18+ LTS recommended; project tested with Node 22 (node -v) |
| npm | Comes with Node.js (npm -v) |
| Git (optional) | Only if cloning from a repository (git --version) |

Tip: Start Laragon and click Start All before setting up the database.

## 2. Get the Project

### Option A: Copy / ZIP

1. Extract or copy the project folder into Laragon’s www directory, for example: C:\laragon\www\DERAS
2. Open a terminal (Laragon → Terminal) and go to the project:

```
cd C:\laragon\www\DERAS
```

### Option B: Clone from GitHub (if a remote exists)

```
cd C:\laragon\www
git clone <YOUR_REPO_URL> DERAS
cd DERAS
```

## 3. Install PHP Dependencies

```
composer install
```

## 4. Setup Environment Variables

1. Copy the example env file:

```
copy .env.example .env
```

2. Generate the application key:

```
php artisan key:generate
```

3. Edit .env and set at least:

```
APP_NAME=DERAS
APP_URL=http://deras.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deras
DB_USERNAME=root
DB_PASSWORD=
```

Notes:
- Laragon MySQL default user is usually root with an empty password.
- Create an empty database named deras in HeidiSQL / phpMyAdmin, or run:

```
CREATE DATABASE deras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

- If you use a Laragon pretty URL, enable deras.test so APP_URL matches the address you open.

## 5. Database Setup

Apply all tables, then load demo / reference data (roles, townships, sample users, etc.):

```
php artisan migrate --seed
```

Or step by step:

```
php artisan migrate
php artisan db:seed
```

## 6. Install Front-end and Build Assets

```
npm install
npm run build
```

For active UI development (optional):

```
npm run dev
```

Keep that terminal open while developing. For normal use, npm run build is enough.

## 7. Storage Link (if file uploads are used)

```
php artisan storage:link
```

## 8. Start the Application

### Recommended (Laragon virtual host)

1. Ensure Laragon is running.
2. Open: http://deras.test
3. Document root should point to the project’s public folder.

### Alternative (Artisan serve)

```
php artisan serve
```

Then open: http://127.0.0.1:8000

## 9. Default Login Accounts (after seed)

| Role | Email | Password |
| --- | --- | --- |
| Super Admin | super@email.com | password |
| Admin | admin@email.com | password |

Change these passwords after first login on any shared or production server.

## 10. Useful Maintenance Commands

```
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

Rebuild assets after CSS/JS changes:

```
npm run build
```

## 11. Troubleshooting

| Problem | Fix |
| --- | --- |
| SQLSTATE / connection refused | Start MySQL in Laragon; check DB_* in .env |
| Blank page / Vite assets missing | Run npm install and npm run build |
| 404 on CSS/JS | Confirm public/build exists; hard-refresh the browser |
| Permission / 500 after deploy | Ensure storage and bootstrap/cache are writable |
| APP_KEY empty | Run php artisan key:generate |
| Wrong URL | Align APP_URL with the address you open in the browser |

## Done

You now have DERAS running locally with seeded Super Admin and Admin accounts.
For day-to-day use, see the User Manual Guide.
