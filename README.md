<div align="center">

# GeniusAuth™ Laravel SDK

### Passwordless identity for Laravel — Passkeys, WebAuthn, OAuth 2.1 & OpenID Connect

**One Identity. Every Application. Zero Passwords.**

[![Packagist Version](https://img.shields.io/packagist/v/geniusauth/laravel?style=for-the-badge&logo=packagist&logoColor=white)](https://packagist.org/packages/geniusauth/laravel)
[![CI](https://img.shields.io/github/actions/workflow/status/GeniusAuth/laravel-sdk/tests.yml?style=for-the-badge&logo=githubactions&logoColor=white&label=CI)](https://github.com/GeniusAuth/laravel-sdk/actions)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![License](https://img.shields.io/packagist/l/geniusauth/laravel?style=for-the-badge&logo=mit&logoColor=white)](LICENSE)

[Documentation](https://docs.geniusauth.com/laravel) · [Report a bug](https://github.com/GeniusAuth/laravel-sdk/issues) · [Request a feature](https://github.com/GeniusAuth/laravel-sdk/issues)

</div>

---

## ✨ Features

- **Passwordless authentication** — Passkeys and WebAuthn, no passwords stored
- **OAuth 2.1 with PKCE** — Authorization Code flow with Proof Key for Code Exchange
- **OpenID Connect** — ID token validation via JWKS with issuer, audience, nonce, and state verification
- **Auto-discovery** — Service provider and facade registered automatically
- **Middleware included** — Protect routes with a single `geniusauth` middleware alias
- **Session management** — Secure session-backed user identity with token storage
- **Zero dependencies on passwords** — Cryptographic authentication only

## 📦 Installation

```bash
composer require geniusauth/laravel
php artisan vendor:publish --tag=geniusauth-config
```

The service provider is auto-discovered by Laravel. No manual registration needed.

## ⚙️ Configuration

Create an application in the [GeniusAuth dashboard](https://docs.geniusauth.com), then set your credentials in `.env`:

```dotenv
GENIUSAUTH_ISSUER=https://auth.geniuspay.tech
GENIUSAUTH_CLIENT_ID=your_client_id
GENIUSAUTH_CLIENT_SECRET=your_client_secret
GENIUSAUTH_REDIRECT_URI=https://app.example.com/auth/genius/callback
```

### Config options

| Key | Default | Description |
| --- | --- | --- |
| `issuer` | `https://auth.geniuspay.tech` | GeniusAuth OIDC issuer URL |
| `client_id` | — | Your application client ID |
| `client_secret` | — | Your application client secret |
| `redirect_uri` | — | Callback URL registered in the dashboard |
| `scopes` | `['openid', 'profile', 'email']` | OpenID Connect scopes to request |
| `session_key` | `geniusauth.user` | Session key for the authenticated identity |

## 🚀 Quick Start

### 1. Protect routes

```php
Route::middleware('geniusauth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/account', [AccountController::class, 'show']);
    Route::post('/settings', [SettingsController::class, 'update']);
});
```

### 2. Trigger sign-in

```php
use GeniusAuth\Laravel\Facades\GeniusAuth;

Route::get('/sign-in', fn () => GeniusAuth::redirect())->name('sign-in');
```

### 3. Access the authenticated user

```php
$user = GeniusAuth::user();

// [
//     'id'     => 'usr_abc123',
//     'email'  => 'alice@example.com',
//     'name'   => 'Alice',
//     'claims' => [...all OIDC claims],
// ]
```

### 4. Logout

```php
GeniusAuth::logout();
return redirect('/');
```

## 🔄 How it works

```
User                Laravel              GeniusAuth
 │                     │                     │
 │  GET /dashboard     │                     │
 │────────────────────►│                     │
 │  302 /auth/genius   │                     │
 │◄────────────────────│                     │
 │  GET /auth/genius   │                     │
 │──────────────────────────────────────────►│
 │  Passkey / WebAuthn │                     │
 │◄──────────────────────────────────────────│
 │  302 /auth/genius/callback?code=...       │
 │────────────────────►│                     │
 │                     │  POST /token        │
 │                     │────────────────────►│
 │                     │  access_token +     │
 │                     │  id_token           │
 │                     │◄────────────────────│
 │                     │  JWKS validation    │
 │                     │  session.put()      │
 │  302 /dashboard     │                     │
 │◄────────────────────│                     │
 │  200 OK             │                     │
 │────────────────────►│                     │
```

## 🛣️ Registered routes

The package automatically registers these routes:

| Method | URI | Name | Description |
| --- | --- | --- | --- |
| `GET` | `/auth/genius` | `geniusauth.login` | Redirects to GeniusAuth authorization endpoint |
| `GET` | `/auth/genius/callback` | `geniusauth.callback` | Handles the OAuth callback and token exchange |
| `POST` | `/auth/genius/logout` | `geniusauth.logout` | Clears session and invalidates tokens |

## 📚 API Reference

| Method | Returns | Description |
| --- | --- | --- |
| `GeniusAuth::redirect()` | `RedirectResponse` | Starts OAuth 2.1 Authorization Code flow with PKCE |
| `GeniusAuth::user()` | `array\|null` | Returns the session-backed authenticated identity |
| `GeniusAuth::logout()` | `void` | Clears GeniusAuth tokens and invalidates the session |

### Example: Complete controller

```php
use GeniusAuth\Laravel\Facades\GeniusAuth;
use Illuminate\Http\Request;

class AuthController
{
    public function signIn()
    {
        return GeniusAuth::redirect();
    }

    public function dashboard(Request $request)
    {
        $user = GeniusAuth::user();

        return view('dashboard', ['user' => $user]);
    }

    public function logout()
    {
        GeniusAuth::logout();

        return redirect()->route('home');
    }
}
```

## 🔒 Security

- **OAuth 2.1 Authorization Code + PKCE** — No implicit flow, no client secrets in URLs
- **JWKS validation** — ID tokens verified against the issuer's JSON Web Key Set
- **Claim verification** — Issuer, audience, nonce, and state validated on every callback
- **No passwords** — Authentication relies exclusively on cryptography (WebAuthn) and one-time links
- **Session regeneration** — Token invalidation and CSRF token regeneration on logout

Never commit client secrets. Report vulnerabilities privately as described in [SECURITY.md](SECURITY.md).

## 🧩 Compatibility

| Requirement | Version |
| --- | --- |
| PHP | 8.3, 8.4 |
| Laravel | 11, 12 |

## 🤝 Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Please use [Conventional Commits](https://www.conventionalcommits.org/) and run:

```bash
composer lint       # Pint formatting check
composer analyse    # PHPStan static analysis
composer test       # PHPUnit test suite
```

## 📄 License

GeniusAuth Laravel SDK is open-sourced software licensed under the [MIT license](LICENSE).

<div align="center">

© 2025 **GENIUS GROUPS SAS** — Built in Côte d'Ivoire 🇨🇮 · Designed for Africa · Ready for the World

</div>