# Repository Guidelines

## Project Scope & Architecture

`toko-engine` is ScriptMedia's reusable e-commerce application for catalog, cart, checkout, orders, and store administration. Keep it a conventional **single-tenant** Laravel application: every deployed store has its own database. Do not add tenant switching or provisioning logic; those belong in the sibling `toko-panel` project. Never modify files outside `toko-engine/` unless explicitly requested.

Plan limits must be accessed through a small `StoreLimitService`, not scattered conditionals. It may read product or payment-gateway limits from the panel's central database, but standalone installations must fall back to unlimited or local configuration.

## Project Structure & Module Organization

Application classes live in `app/`; place class-based Livewire components in `app/Livewire/` and matching templates in `resources/views/livewire/`. HTTP and settings routes are in `routes/`. Keep migrations, factories, and seeders under `database/`. Frontend sources belong in `resources/css/` and `resources/js/`; only publicly served static assets go in `public/`. Tests are split between `tests/Feature/` and `tests/Unit/`.

## Build, Test, and Development Commands

- `composer setup` installs PHP/Node dependencies, prepares `.env`, migrates, and builds assets.
- `composer dev` starts Laravel, the queue listener, and Vite concurrently.
- `composer test` runs Pint checks, PHPStan level 7, and the full test suite.
- `composer lint` fixes PHP formatting; `composer types:check` runs static analysis.
- `npm run build` creates production frontend assets.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF, four spaces (two for YAML), and final newlines. Use Laravel Pint's `laravel` preset and PSR-4 namespaces. Name classes in PascalCase, methods/variables in camelCase, database columns in snake_case, and Blade files in kebab-case. Use class-based Livewire components; do not introduce Volt.

## Testing Guidelines

Use Pest 5/PHPUnit tests and `RefreshDatabase` for database behavior. Name tests by feature and behavior, for example `tests/Feature/Checkout/PlaceOrderTest.php`. Cover happy paths, validation, authorization, failures, and every bug regression. CI uses PHP 8.3 and Node 22 and runs `composer ci:check`.

## Design, Security & Contributions

Follow `sewa-toko-online.html`: Questrial, navy `#0B2545`, tosca `#2CA6A4`, orange `#F4A300`, off-white `#F4FAFA`, 18px cards, thin borders, solid pill badges, and navy gradient heroes. Never commit `.env`, credentials, customer data, or generated databases.

History currently contains only `init`; use concise imperative commits such as `Add cart quantity validation`. Pull requests should explain scope, migrations/config changes, and verification, link relevant issues, and include screenshots for UI work.
