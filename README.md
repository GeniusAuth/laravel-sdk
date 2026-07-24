<div align="center">

# GeniusAuth™ Laravel SDK

### Passwordless identity for Laravel

**One Identity. Every Application. Zero Passwords.**

[![Packagist Version](https://img.shields.io/packagist/v/geniusauth/laravel?style=for-the-badge)](https://packagist.org/packages/geniusauth/laravel)
[![CI](https://img.shields.io/github/actions/workflow/status/GeniusAuth/laravel-sdk/tests.yml?style=for-the-badge&label=CI)](https://github.com/GeniusAuth/laravel-sdk/actions)
[![License](https://img.shields.io/packagist/l/geniusauth/laravel?style=for-the-badge)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge)](https://www.php.net/)

[Documentation](https://docs.geniusauth.com/laravel) · [Report a bug](https://github.com/GeniusAuth/laravel-sdk/issues) · [Request a feature](https://github.com/GeniusAuth/laravel-sdk/issues)

</div>

## Installation

```bash
composer require geniusauth/laravel
php artisan vendor:publish --tag=geniusauth-config
```

## Configuration

Create an application and redirect URI in the [GeniusAuth dashboard](https://docs.geniusauth.com), then configure its credentials:

```dotenv
GENIUSAUTH_ISSUER=https://auth.geniuspay.tech
GENIUSAUTH_CLIENT_ID=your_client_id
GENIUSAUTH_CLIENT_SECRET=your_client_secret
GENIUSAUTH_REDIRECT_URI=https://app.example.com/auth/genius/callback
```

The package discovers its service provider automatically. It registers the `geniusauth.login`, `geniusauth.callback`, and `geniusauth.logout` routes.

## Quick Start

Protect a route with the included middleware:

```php
Route::get('/account', AccountController::class)->middleware('geniusauth');
```

Start sign-in from any controller:

```php
use GeniusAuth\Laravel\Facades\GeniusAuth;

return GeniusAuth::redirect();
```

Read the authenticated identity after callback processing:

```php
$user = GeniusAuth::user();

// ['id' => '...', 'email' => '...', 'name' => '...', 'claims' => [...]]
```

## API

| Method | Description |
| --- | --- |
| `GeniusAuth::redirect()` | Starts OAuth 2.1 Authorization Code flow with PKCE. |
| `GeniusAuth::user()` | Returns the session-backed authenticated identity, or `null`. |
| `GeniusAuth::logout()` | Clears GeniusAuth tokens and invalidates the current session. |

See the full [Laravel SDK API documentation](https://docs.geniusauth.com/laravel).

## Security

The SDK uses OAuth 2.1 Authorization Code with PKCE, validates ID tokens against the issuer JWKS, verifies issuer, audience, nonce, and state, and does not handle passwords. Never commit client secrets. Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md).

## Compatibility

- PHP 8.3+
- Laravel 11 and 12

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Please use [Conventional Commits](https://www.conventionalcommits.org/) and run:

```bash
composer lint
composer analyse
composer test
```

## License

GeniusAuth Laravel SDK is open-sourced software licensed under the [MIT license](LICENSE).