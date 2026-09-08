<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Production deployment

### Server requirements

- PHP 8.3 or newer with the extensions required by Laravel.
- Composer 2.x.
- Node.js and npm for compiling frontend assets.
- MySQL or another database supported by the configured Laravel connection.
- A web server configured to serve the application's `public/` directory.

### Windows 10 with Laragon

Run Laragon as an administrator and start Apache and MySQL. Use Laragon's Terminal so that the configured PHP, Composer, Node.js, and npm versions are available on `PATH`.

For a new server, place the project at `C:\laragon\www\eld-is` and create the database from Laragon's HeidiSQL or MySQL client:

```sql
CREATE DATABASE whitelist_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then run these commands in Laragon Terminal:

```powershell
cd C:\laragon\www\eld-is

composer install --no-dev --optimize-autoloader
Copy-Item .env.example .env
php artisan key:generate

npm ci
npm run build

php artisan migrate --force
php artisan storage:link
php artisan db:seed --force
php artisan optimize
```

Edit `C:\laragon\www\eld-is\.env` before running the migrations. For a default Laragon MySQL installation, the database section will usually be:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=http://eld-is.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whitelist_manager
DB_USERNAME=root
DB_PASSWORD=
```

Replace the default Laragon database username and blank password if your MySQL installation uses a separate production account. For an internet-facing deployment, use HTTPS and set `APP_URL` to the public HTTPS URL.

In Laragon, configure Apache's virtual host to use `C:\laragon\www\eld-is\public` as its document root. Do not serve `C:\laragon\www\eld-is` directly. You can configure this through Laragon's Apache site configuration, then reload Apache from the Laragon menu. Verify that `http://eld-is.test` opens the application.

The Windows equivalent of the required writable directories is to grant the Apache service account Modify permission on `storage` and `bootstrap\cache`. In File Explorer, right-click each directory, select **Properties > Security > Edit**, and grant Modify permission to the account running Apache. Also confirm that `storage\app\whitelists` exists:

```powershell
New-Item -ItemType Directory -Force storage\app\whitelists
```

For subsequent deployments, stop Apache briefly if files are locked, replace the application files, then run:

```powershell
cd C:\laragon\www\eld-is
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Restart Apache from Laragon after deployment. If this application later uses queued jobs, run `php artisan queue:work --sleep=3 --tries=3 --max-time=3600` through Windows Task Scheduler, NSSM, or another Windows service manager. Use `php artisan queue:restart` after each deployment.

### Initial deployment

Run these commands from the application directory:

```bash
git clone <repository-url> eld-is
cd eld-is

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

npm ci
npm run build

php artisan migrate --force
php artisan storage:link
php artisan db:seed --force

php artisan optimize
```

Before running the commands above, edit `.env` and set production values:

```dotenv
APP_NAME="ELD IS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whitelist_manager
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

Use a strong, unique database password and keep `.env` outside version control. `php artisan key:generate` is only for a new installation; preserve the existing `APP_KEY` during later deployments.

The database user must have permission to create and alter the application's tables. The `db:seed` command creates or updates the initial super-admin account from `DatabaseSeeder`; change that account's password immediately after the first login, or provision an administrator through another controlled process.

### Files and permissions

Ensure the web-server user can read the project and write to these directories:

```bash
chmod -R ug+rwX storage bootstrap/cache
mkdir -p storage/app/whitelists
chmod ug+rwX storage/app/whitelists
```

The application writes generated whitelist files to `storage/app/whitelists` by default. If the administrator configures a custom output directory, that directory must also exist and be writable by the web-server user.

Configure the web server's document root as `<application-directory>/public`. Do not point it at the repository root, because that could expose `.env` and other private files.

### Subsequent deployments

After pulling the new release, run:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

If the application is served by PHP-FPM or another long-running PHP process, reload it after deployment so it uses the new release. When using the database queue driver, run the worker under Supervisor, systemd, or another process manager:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

After each deployment, tell the process manager to restart the worker, or signal it with:

```bash
php artisan queue:restart
```

For zero-downtime deployments, run the commands against the new release before switching the web-server symlink, and use a process manager such as Supervisor or systemd for queue workers.

### Production checks

```bash
php artisan about
php artisan migrate:status
php artisan config:show app
```

Confirm that the site loads over HTTPS, login works, migrations are up to date, generated whitelist files can be written, and application logs are being collected from `storage/logs`.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
