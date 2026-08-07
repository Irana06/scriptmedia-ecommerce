# Repository Guidelines

## Project Scope & Architecture

`toko-engine` is ScriptMedia's reusable e-commerce application for catalog, cart, checkout, orders, and store administration. It supports two explicit runtime modes. In ScriptMedia-hosted mode (`TENANCY_ENABLED=true`), it serves every public store domain and uses `stancl/tenancy` to select the tenant database by domain. In standalone/self-hosted mode (`TENANCY_ENABLED=false`), tenancy middleware is completely disabled and the application behaves like a conventional single-database Laravel store. Database creation and provisioning orchestration still belong in the sibling `toko-panel` project. Never modify files outside `toko-engine/` unless explicitly requested.

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

## Final Product & Architecture Decisions

These decisions come from project concept documents 14 and 15. They override any older instruction that conflicts with them. Interpret the documents in order: concept 13, correction 14, then correction 15.

### Domain Serving & Tenancy

- `toko-engine` is the only application that serves public tenant storefront traffic. Wildcard subdomains such as `*.scriptmedia.id` and all client custom domains must resolve to the `toko-engine` deployment.
- `toko-panel` has one separate central domain such as `panel.scriptmedia.id`. It must never render storefront pages or receive requests for store domains.
- `stancl/tenancy` belongs in `toko-engine`, not `toko-panel`. When `TENANCY_ENABLED=true`, `InitializeTenancyByDomain` resolves the domain-to-tenant record from the panel's central database through a read connection, then switches the default connection to that tenant's database.
- Hosted tenants share the `toko-engine` codebase, but each client has a fully separate database. One client equals one tenant, one store, and one tenant database; a dedicated physical server per client is not required.
- When `TENANCY_ENABLED=false`, do not initialize tenancy or query tenant domains. Use the normal default database so sold templates can run standalone.
- `toko-panel` remains responsible for provisioning: creating the database and central records, running `toko-engine` migrations against the new tenant database, registering domains, and creating the tenant owner account. Do not move provisioning orchestration into `toko-engine`.
- DNS must route wildcard `*.scriptmedia.id` and client custom-domain CNAMEs to `toko-engine`; only the panel domain routes to `toko-panel`. Both apps may share a physical server but must use separate virtual hosts/app entry points.

### Accounts, Plans & Feature Rules

- The owner intentionally has two separate accounts. The central-DB account signs in to the panel billing/ticket portal; a different user record in the tenant DB signs in to the store administration area. Provisioning creates both using the same email/contact identity. Do not introduce SSO or shared cross-application tokens unless explicitly requested later.
- Store limits must continue to come from central `plans` records through `StoreLimitService`. Standalone mode may fall back to unlimited or local configuration.
- Midtrans uses one shared merchant account. Starter must send `enabled_payments=['other_qris']`; Standard permits `other_qris` plus VA channels including `bca_va`, `bni_va`, `bri_va`, and `permata_va`; Pro leaves all active channels available, including credit cards and other e-wallets. Do not change Starter back to `gopay`.
- Content-change requests are final, not provisional: usage is the number of `content_change_requests` inside the active subscription's `current_period_start` through `current_period_end`, compared with `plans.content_request_quota`. The existing panel behavior is intentional.
- Do not require separate annual-price columns. Every annual price is calculated dynamically as `(price_platform + price_care_monthly) * 10`, while the subscription remains active for 12 months.
- Canonical monthly plan values are: Starter `150000 + 150000`, Standard `150000 + 350000`, and Pro `150000 + 550000` rupiah for platform plus care respectively.
- The business workflow for selling template source code is deliberately deferred. Preserve standalone capability, but do not add licensing, one-time-sale, setup-service, or optional-care-plan models unless explicitly requested.
