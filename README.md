# Laravel Web Installer

[![Laravel Web Installer](https://github.com/rashidlaasri/LaravelInstaller/assets/36804104/5fea5ade-7c97-43bb-b6a9-89cd8d2a0bdf)](https://github.com/wikigods/LaravelInstaller)

A simple web-based installer for Laravel applications.

This is the Wikigods-maintained fork of the original [LaravelInstaller by Rachid Laasri and Jeremy Kenedy](https://github.com/rashidlaasri/LaravelInstaller). It continues their work with gratitude to the original authors and the [contributors to the upstream project](https://github.com/rashidlaasri/LaravelInstaller/graphs/contributors).

The Composer package is `wikigods/laravel-installer`, and the PHP namespace is `Wikigods\LaravelInstaller`.

## Installation

```bash
composer require wikigods/laravel-installer
```

If your application currently requires `rachidlaasri/laravel-installer`, remove that package and require the new package name:

```bash
composer remove rachidlaasri/laravel-installer
composer require wikigods/laravel-installer
```

If your application refers directly to package classes, update imports from `RachidLaasri\LaravelInstaller` to `Wikigods\LaravelInstaller`.

## Updater authorization

The web updater requires an authenticated user and the Laravel Gate ability configured as `updaterPermission`. The default ability is `p_admin.installer-updates.index`.

Publish the package configuration and change `updaterPermission` in `config/installer.php` if your application uses a different Gate ability. Define that ability in your application, for example in `App\Providers\AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('p_admin.installer-updates.index', function ($user) {
        return (bool) $user->is_admin;
    });
}
```

The `is_admin` check is only an example; use a field or authorization rule that exists in your application. The package does not add a roles system. The first installation happens before application users can authenticate, so protect `/install` at the web server or run the installation in a controlled environment.

## nwidart/laravel-modules

When `nwidart/laravel-modules` is installed with migration auto-discovery enabled, the installer and updater include migration paths registered by enabled modules. The update overview counts only module and application migrations that have not run yet. Migration execution continues through Laravel's standard `migrate` command, which the Modules service provider extends with enabled module paths.

