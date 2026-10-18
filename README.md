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

