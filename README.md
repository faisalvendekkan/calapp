# BATAQAH

BATAQAH is a multi-tenant digital business profile platform built with Laravel. Customers can create branded public profiles, publish contact and business details, receive leads, and connect NFC cards. The foundation includes tenant isolation, profile lifecycle controls, analytics events, subscription data structures, and a versioned API.

## Current milestone

This branch contains the production-oriented platform foundation and the first end-to-end profile workflow. The existing calculator remains on `main` until the Laravel deployment environment is configured and this branch is approved.

Implemented:

- Registration with automatic organization creation
- Organization membership and profile authorization
- Customer profile create, edit, publish, and delete workflow
- Public profiles at `/p/{slug}`
- Lead capture and privacy-conscious visit analytics
- Secure NFC redirects at `/n/{token}`
- Sanctum-protected `/api/v1/profiles` endpoints
- Core SaaS schema for plans, subscriptions, payments, templates, branches, roles, and audit logs
- Responsive blue/red BATAQAH interface

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for the schema, routes, modules, and delivery milestones.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js 20+, MySQL 8+, Redis, and an S3-compatible object store.

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure database, Redis, mail, and S3 values in .env
php artisan migrate
npm install
npm run build
php artisan serve
```

For a zero-configuration local test run, use SQLite in `.env.testing` or set `DB_CONNECTION=sqlite` and create `database/database.sqlite`.

## Quality checks

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

## Production deployment

Point the web server document root to `public/`, provide a production `.env`, run `composer install --no-dev --optimize-autoloader`, execute migrations during a maintenance window, build frontend assets, and run the queue worker. Never commit credentials or the application key.

## Security

Tenant access is enforced through organization membership and policies. Public profiles expose only published records. NFC cards use random tokens. Analytics store a one-way IP fingerprint instead of a raw IP address. Report security issues privately to the repository owner.
