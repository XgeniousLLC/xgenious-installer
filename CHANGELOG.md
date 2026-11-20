# Changelog

## v2.9.1 — 2026-11-20

### Fixed

- Render the install callback's message on the success screen. The new wizard
  ignores the generic `msg` on success, so the layout follow-up instructions
  are now returned as `layout_note` and shown in the finish step.

## v2.9.0 — 2026-11-20

### Added

- PostgreSQL tenant layout selector in the database step, shown when
  `database_type` is `pgsql` and `multi_tenant` is true.
- `database_layouts` (layout key → label) and `database_layout` (default)
  config keys. Set `database_layouts` to an empty array to hide the selector.
- `install_callback` config key. The selected layout and database credentials
  are passed to `apply(string $layout, array $db): array` on the configured
  class after the SQL dump import and admin creation.
- `database_layout` and `layout_ok` fields in the install response.
- `Xgenious\Installer\Support\DatabaseLayout` helper for resolving layouts and
  invoking the product callback without throwing on callback failures.
- README documentation for the layout selector, callback contract, and
  Laravel 11/12 middleware registration.

### Tests

- `DatabaseLayoutTest` covers layout resolution, fallback, callback invocation,
  disabled layouts, missing callbacks, and failure propagation.
- `InstallerViewTest` covers selector visibility for pgsql/mysql and
  multi-tenant/single-tenant installs.

## v2.8.1 — 2026-11-16

### Fixed

- Surface the actual database import error instead of a generic failure.
- Fix SQL dump statement handling for Laravel 11+.
