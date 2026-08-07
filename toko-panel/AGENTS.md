# Repository Guidelines

## Project Scope & Architecture

`toko-panel` is ScriptMedia's central control panel for tenants, plans, subscriptions, invoices, content-change tickets, and store provisioning. It runs only on its own central domain for ScriptMedia admins and tenant owners; it never serves a tenant storefront or receives traffic for a store domain. Never edit files outside `toko-panel/` unless explicitly requested, especially `../toko-engine/`.

The platform uses one central database for plans, tenants, subscriptions, domains, and billing, plus a separate database per client. Tenant databases follow the `toko-engine` migration structure. Runtime domain resolution and database switching through `stancl/tenancy` belong in `toko-engine`; this panel only orchestrates provisioning. Package limits (products, gateways, support quotas, and similar rules) must come from plan records; never hardcode them.

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

## Final Product & Architecture Decisions

These decisions come from project concept documents 14 and 15. They override any older instruction that conflicts with them. Interpret the documents in order: concept 13, correction 14, then correction 15.

### Domain Serving & Tenancy

- `toko-engine` is the only application that serves public tenant storefront traffic. Wildcard subdomains such as `*.scriptmedia.id` and all client custom domains must resolve to the `toko-engine` deployment.
- `toko-panel` has one separate central domain such as `panel.scriptmedia.id`, used only by ScriptMedia admins and owners for billing and tickets. It must never render storefront pages or receive requests for store domains.
- `stancl/tenancy` and `InitializeTenancyByDomain` belong in `toko-engine`, not `toko-panel`. Remove or avoid panel-side tenant-domain runtime routes and middleware; the presence of only a panel tenant health route does not constitute a working multi-tenant storefront.
- Hosted tenants share the `toko-engine` codebase, but each client has a fully separate database. One client equals one tenant, one store, and one tenant database; a dedicated physical server per client is not required.
- The panel's provisioning responsibility is to create the central records and tenant database, run `toko-engine` migrations against it, register its subdomain/custom domain, and create the tenant owner account. The storefront application itself remains in `toko-engine`.
- DNS must route wildcard `*.scriptmedia.id` and client custom-domain CNAMEs to `toko-engine`; only the panel domain routes to `toko-panel`. Both apps may share a physical server but must use separate virtual hosts/app entry points.
- `toko-engine` must be able to disable tenancy with `TENANCY_ENABLED=false` for standalone/self-hosted template installations. The panel does not control standalone requests.

### Accounts, Billing & Feature Rules

- The owner intentionally has two separate accounts. The central-DB account signs in to this panel's billing/ticket portal; a different user record in the tenant DB signs in to the store administration area. Provisioning creates both using the same email/contact identity. Do not introduce SSO or shared cross-application tokens unless explicitly requested later.
- Midtrans uses one shared merchant account. Starter must use `other_qris`; Standard permits `other_qris` plus VA channels including `bca_va`, `bni_va`, `bri_va`, and `permata_va`; Pro permits all active channels, including credit cards and other e-wallets. Do not change Starter back to `gopay`.
- Content-change request behavior is final: count `content_change_requests` whose usage falls within the active subscription's inclusive `current_period_start` through `current_period_end`, then compare the count with `plans.content_request_quota`. The existing panel implementation is intentional and must not be reverted to a placeholder-only table.
- Keep plan pricing simple. Do not add or depend on `price_care_annual` or `price_platform_annual`. Generate an annual invoice dynamically as `(price_platform + price_care_monthly) * 10` and grant a 12-month subscription period.
- Canonical seed values are: Starter `price_platform=150000`, `price_care_monthly=150000`; Standard `price_platform=150000`, `price_care_monthly=350000`; Pro `price_platform=150000`, `price_care_monthly=550000`.
- The business workflow for selling template source code is deliberately deferred. Do not create licensing, one-time-sale, setup-service, or optional-care-plan models unless explicitly requested.
