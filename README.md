<div align="center">

# GeniusAuth™ Laravel SDK

### Official Laravel Integration for GeniusAuth™

**One Identity. Every Application. Zero Passwords.**

<p>

<img src="https://img.shields.io/packagist/v/geniusauth/laravel?style=for-the-badge">
<img src="https://img.shields.io/packagist/dt/geniusauth/laravel?style=for-the-badge">
<img src="https://img.shields.io/github/actions/workflow/status/geniusauth/laravel-sdk/tests.yml?style=for-the-badge">
<img src="https://img.shields.io/badge/Laravel-12-red?style=for-the-badge">
<img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge">
<img src="https://img.shields.io/badge/OIDC-Ready-success?style=for-the-badge">

</p>

</div>

---

## Installation

```bash
composer require geniusauth/laravel
```

---

## Publish

```bash
php artisan vendor:publish
```

---

## Configuration

```php
GENIUSAUTH_CLIENT_ID=

GENIUSAUTH_CLIENT_SECRET=

GENIUSAUTH_REDIRECT_URI=
```

---

## Login

```php
return GeniusAuth::redirect();
```

---

## Callback

```php
$user = GeniusAuth::user();
```

---

## Features

- Laravel Native
- Middleware
- Authentication Guards
- OAuth 2.1
- OpenID Connect
- Passkeys
- WebAuthn
- Session Management
- Automatic Refresh Tokens

---

## Requirements

- PHP 8.3+
- Laravel 12+

---

## Documentation

👉 https://docs.geniusauth.com/laravel

---

## License

Copyright © GENIUS GROUPS SAS.