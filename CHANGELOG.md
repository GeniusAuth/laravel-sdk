# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/).

## [0.1.2] - 2026-07-25

### Changed

- Refactored to modular Domain-Driven Design (DDD) architecture.
- Split `IdentityLinkingService` into `LinkFlowService` (application layer) and `GeniusAuthSyncClient` (infrastructure).
- Extracted `OidcTokenValidator` from `OidcClientService` to separate domain validation logic from infrastructure concerns.
- `StaffSyncService` now depends on `UserRepositoryInterface` instead of reading config directly.
- `GeniusAuthServiceProvider` moved to `Providers\` namespace with contract-to-implementation bindings.
- All controllers and facades now inject interfaces instead of concrete classes.

### Added

- `Contracts/` directory: `OidcClientInterface`, `TokenValidatorInterface`, `LinkFlowInterface`, `SyncClientInterface`, `StaffSyncInterface`, `UserRepositoryInterface`.
- `DTOs/` directory: `AuthenticatedUserDTO`, `OidcClaimsDTO`, `UserSyncDTO` — immutable value objects.
- `Exceptions/` directory: `GeniusAuthException` (base), `OidcException`, `SyncFailedException`.
- `Infrastructure/` directory: `GeniusAuthSyncClient`, `ConfigUserRepository`.
- Unit tests for DTOs (9 tests) and exceptions (5 tests).
- Expanded feature tests for service provider bindings (11 tests).
- Backward-compatibility aliases marked `@deprecated` with removal planned for v1.0.

### Deprecated

- Direct injection of concrete service classes (`OidcClientService`, `LinkFlowService`, `StaffSyncService`, `GeniusAuthSyncClient`, `OidcTokenValidator`, `ConfigUserRepository`). Inject the corresponding interface instead. Aliases will be removed in v1.0.

## [0.1.0] - 2026-07-24

### Added

- Laravel service provider, facade, routes, and authentication middleware.
- OAuth 2.1 Authorization Code flow with PKCE.
- OpenID Connect discovery, JWKS-backed ID token validation, and session management.
- Laravel 11 and 12 support on PHP 8.3 and 8.4.

[0.1.2]: https://github.com/GeniusAuth/laravel-sdk/releases/tag/v0.1.2
[0.1.0]: https://github.com/GeniusAuth/laravel-sdk/releases/tag/v0.1.0
