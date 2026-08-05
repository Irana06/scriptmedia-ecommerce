# Repository Guidelines

## Project Scope & Architecture

`toko-panel` is ScriptMedia's internal control panel for tenants, plans, subscriptions, invoices, content-change tickets, and store provisioning. It is the multi-tenant layer above the separate `toko-engine` application. Never edit files outside `toko-panel/` unless explicitly requested, especially `../toko-engine/`.

Tenancy uses one central database for plans, tenants, subscriptions, and billing, plus a separate database per client through `stancl/tenancy`. Tenant databases follow the `toko-engine` migration structure. Package limits (products, gateways, support quotas, and similar rules) must come from plan records; never hardcode them.

## Project Structure & Module Organization

Laravel application code lives in `app/`; class-based Livewire components belong in `app/Livewire/` and their Blade views in `resources/views/livewire/`. Routes are split between `routes/web.php`, `routes/settings.php`, and console routes. Database migrations, factories, and seeders live under `database/`. Frontend sources are in `resources/css/` and `resources/js/`, while public static files belong in `public/`. Put Pest tests in `tests/Feature/` or `tests/Unit/`.

## Build, Test, and Development Commands

- `composer setup` installs dependencies, creates `.env`, generates the key, migrates, and builds assets.
- `composer dev` runs the Laravel server, queue listener, and Vite together.
- `composer test` clears configuration, checks Pint formatting, runs PHPStan level 7, and executes Pest.
- `composer lint` fixes PHP formatting; `composer types:check` runs static analysis only.
- `npm run build` creates production frontend assets.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF endings, four-space indentation (two for YAML), and a final newline. Use Laravel Pint's `laravel` preset and PSR-4 namespaces. Name PHP classes in PascalCase, methods and variables in camelCase, database fields in snake_case, and Blade files in kebab-case. Use class-based Livewire components pinned by `composer.lock`; do not introduce Volt.

## Testing Guidelines

Use Pest 5. Name tests by behavior and keep feature tests close to the affected domain, for example `tests/Feature/Billing/InvoiceTest.php`. Add regression coverage for every bug fix and tests for central-versus-tenant database boundaries. No coverage threshold is configured; prioritize meaningful success, validation, authorization, and failure paths.

## Design, Security & Contributions

Match `sewa-toko-online.html`: Questrial, navy `#0B2545`, tosca `#2CA6A4`, orange `#F4A300`, off-white `#F4FAFA`, 18px cards, thin borders, pill badges, and navy gradient heroes. Never commit `.env`, credentials, tenant data, or generated databases.

Git history currently contains only `init`, so no established commit convention exists. Use concise imperative messages such as `Add tenant invoice status filter`. Pull requests should explain scope, migrations, tenancy impact, and verification; link issues and include screenshots for UI changes.
