# Project Management System

Laravel 12 + Sanctum API + Filament Admin dashboard for managing projects, tasks, comments, and role-based workflows.

## Tech Stack

- PHP `^8.2`
- Laravel `^12`
- Filament `^3.2`
- Laravel Sanctum `^4.0`
- SQLite 

## Features

- Role-based access with 3 roles: `admin`, `manager`, `developer`
- Project and Task authorization using policies
- REST API secured by Sanctum tokens
- Filament admin panel at `/admin`
- Task assignment notifications (database notifications)
- Dashboard widgets for:
	- Role-aware stats
	- Developer assigned tasks (status updates)
	- User notifications (read/unread)

## Database Models

- `users` (`role`: admin, manager, developer)
- `projects` (`created_by`, `deadline`, `status`)
- `tasks` (`project_id`, `assigned_to`, `priority`, `status`, soft deletes)
- `comments` (`task_id`, `user_id`, `content`, soft deletes)
- `notifications` (Laravel database notifications)

## Project Setup

1. Install dependencies

```bash
composer install
npm install
```

2. Configure env and app key

```bash
cp .env.example .env
php artisan key:generate
```

3. Ensure SQLite file exists

```bash
touch database/database.sqlite
```

4. Run migrations + seeders

```bash
php artisan migrate --seed
```

5. Start the app

```bash
php artisan serve
```

Optional frontend build:

```bash
npm run dev
```

## Seeded Users

- Manager: `manager@test.com` / `password`
- Developer: `dev@test.com` / `password`
- Admin: `admin@test.com` / `password123`

## Access Points

- Web root redirects to Filament: `/ -> /admin`
- Filament dashboard/login: `/admin`
- API base URL: `/api`



