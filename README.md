# Sales CRM Lite

> A lightweight CRM for small sales teams, built with custom PHP 8.2 MVC, MySQL/MariaDB, and Tailwind CSS.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%2FMariaDB-supported-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![PHPUnit](https://img.shields.io/badge/tests-PHPUnit-8892BF?style=flat-square)
![Status](https://img.shields.io/badge/status-portfolio_MVP-green?style=flat-square)

Sales CRM Lite is a portfolio-grade CRM application that demonstrates secure, maintainable PHP application architecture without a framework. It includes authentication, role-based access, user management, CRM modules, reporting, exports, audit logging, Tailwind UI, automated tests, and production deployment documentation.

The project is intentionally single-tenant and framework-free so the core application architecture is visible: front controller, custom router, middleware, controllers, PDO models, PHP views, service helpers, SQL migrations, and PHPUnit coverage.

## Feature Overview

| Area | Implemented |
| --- | --- |
| Authentication | Login, logout, bcrypt password hashes, CSRF protection, session regeneration, hardened logout |
| Auth hardening | Database-backed failed login throttling, last-login tracking, password rehash lifecycle |
| Roles | `admin` and `sales_rep` role behavior with scoped CRM visibility |
| User management | Admin-only user list, create, edit, activate/deactivate, password reset |
| Leads | CRUD, assignment, status, priority, search, filters, sorting, pagination |
| Lead conversion | Transaction-safe lead-to-customer conversion with conversion tracking |
| Customers | CRUD, assignment, status, search, filters, sorting, pagination |
| Follow-ups | Lead/customer tasks, due dates, overdue states, completion/cancellation, filters |
| Dashboard | Scoped CRM KPIs, recent records, upcoming follow-ups |
| Activity log | Immutable audit trail for important auth and CRM actions |
| Reports | Scoped CRM summary insights and admin sales rep performance table |
| CSV exports | Leads, customers, follow-ups, activities, and report summary exports |
| UI | Tailwind-based SaaS-style app shell, tables, forms, badges, pagination, flash messages |
| Deployment | Production checklist, Ubuntu/Nginx/PHP-FPM/MySQL docs, HTTPS guidance |

## Screenshots

Screenshots are intentionally kept as placeholders until final portfolio captures are added. Recommended paths:

| View | Placeholder path |
| --- | --- |
| Dashboard KPIs | `docs/assets/screenshots/dashboard.png` |
| Leads listing | `docs/assets/screenshots/leads-index.png` |
| Lead detail and conversion | `docs/assets/screenshots/lead-detail.png` |
| Customers listing | `docs/assets/screenshots/customers-index.png` |
| Follow-ups board/list | `docs/assets/screenshots/follow-ups-index.png` |
| Activity log | `docs/assets/screenshots/activity-log.png` |
| Reports | `docs/assets/screenshots/reports.png` |
| Login | `docs/assets/screenshots/login.png` |

See [docs/screenshots.md](docs/screenshots.md) for the capture checklist.

## Architecture Overview

Sales CRM Lite uses a custom MVC structure:

- [public/index.php](public/index.php) is the front controller for every request.
- [config/routes.php](config/routes.php) registers route definitions and middleware tags.
- [src/Core/Router.php](src/Core/Router.php) matches HTTP method + URI patterns and dispatches controllers.
- [src/Controllers](src/Controllers) keeps request handling, validation coordination, redirects, and view rendering.
- [src/Models](src/Models) owns SQL queries and uses PDO prepared statements.
- [src/Views](src/Views) contains plain PHP views, layouts, and partials.
- [src/Middleware](src/Middleware) enforces authentication and CSRF checks.
- [src/Services](src/Services) contains small cross-cutting helpers such as activity logging.

Role scoping is enforced server-side. Admin users can access global CRM data; sales reps are limited to assigned records where applicable. See [docs/architecture.md](docs/architecture.md) for a concise architecture walkthrough.

## Security Highlights

- Passwords use PHP password hashing APIs with bcrypt-compatible hashes.
- `password_needs_rehash()` is handled during successful login.
- Session IDs regenerate on login.
- Logout destroys the server-side session and expires the browser cookie.
- Session cookies are `HttpOnly`, `SameSite=Strict`, and Secure in production.
- POST actions are protected by CSRF middleware.
- SQL access uses PDO prepared statements.
- Login throttling locks repeated failed attempts for 15 minutes.
- Raw exceptions are hidden in production and logged to `storage/logs/app.log`.
- Global security headers include frame, content-type, referrer, CSP, and production-only HSTS.
- Password hashes and sensitive secrets are not exposed in views or exports.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.2+ |
| Architecture | Custom MVC with PSR-4 autoloading |
| Database | MySQL 8.0+ or MariaDB 10.6+ |
| Data access | PDO prepared statements |
| Frontend | PHP views with Tailwind CSS |
| Tooling | Composer, PHPUnit, npm, Tailwind CLI |
| Deployment target | Ubuntu VPS, Nginx, PHP-FPM, MySQL/MariaDB, HTTPS |

## Quick Start

Prerequisites:

- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or MariaDB 10.6+
- Node.js and npm

Install dependencies:

```bash
composer install
npm install
```

Create your local environment file:

```bash
cp .env.example .env
```

Set the database values in `.env`:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_crm
DB_USERNAME=root
DB_PASSWORD=
```

## Database Setup

Create the database:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Run migrations in order:

```bash
mysql -u root sales_crm < database/migrations/001_create_users_table.sql
mysql -u root sales_crm < database/migrations/002_create_failed_logins_table.sql
mysql -u root sales_crm < database/migrations/003_create_leads_table.sql
mysql -u root sales_crm < database/migrations/004_create_customers_table.sql
mysql -u root sales_crm < database/migrations/005_add_lead_conversion_fields.sql
mysql -u root sales_crm < database/migrations/006_create_follow_ups_table.sql
mysql -u root sales_crm < database/migrations/007_create_activities_table.sql
```

Seed a local admin account:

```bash
mysql -u root sales_crm < database/seeders/001_seed_admin_user.sql
```

Optional demo CRM data:

```bash
mysql -u root sales_crm < database/seeders/002_seed_demo_crm_data.sql
```

Add `-p` to the MySQL commands if your local MySQL user requires a password.

## Demo Login

Both demo accounts use the local development password `password`.

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@example.com | password |
| Sales Rep | sales@example.com | password |

Change these credentials before using the app outside local development.

## Build And Run

Build the compiled Tailwind stylesheet:

```bash
npm run build:css
```

Run the Tailwind watcher while editing UI files:

```bash
npm run dev:css
```

Start the PHP development server:

```bash
php -S localhost:8000 -t public
```

Open [http://localhost:8000](http://localhost:8000).

The generated CSS file is written to `public/assets/css/app.css` and is ignored by Git.

## Tests And Quality

Run the full test suite:

```bash
composer test
```

Additional scripts:

```bash
composer test:unit
composer test:integration
```

Current coverage focus:

- Foundation/autoload smoke checks
- Authentication hardening behavior
- Role validation and admin safety rules
- Lead, customer, follow-up validation and scoping helpers
- Dashboard and report shape/scoping logic
- Activity log validation and scoped access helpers
- CSV export helpers and safe filenames
- Production readiness checks

## Deployment

Production expectations:

- Web root points to `public/`.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- HTTPS is required.
- `storage/logs` is writable by the PHP/web server user.
- Dependencies are installed with `composer install --no-dev --optimize-autoloader`.
- CSS is built with `npm run build:css`.

Deployment docs:

- [docs/production-checklist.md](docs/production-checklist.md)
- [docs/oracle-cloud-deployment.md](docs/oracle-cloud-deployment.md)
- [docs/nginx-example.conf](docs/nginx-example.conf)
- [docs/production-env-example.md](docs/production-env-example.md)

## Documentation Map

| Document | Purpose |
| --- | --- |
| [docs/architecture.md](docs/architecture.md) | Concise current architecture overview |
| [docs/screenshots.md](docs/screenshots.md) | Portfolio screenshot checklist |
| [docs/database-design.md](docs/database-design.md) | Database design notes |
| [docs/security-concept.md](docs/security-concept.md) | Security planning notes |
| [docs/production-checklist.md](docs/production-checklist.md) | Production readiness checklist |
| [docs/oracle-cloud-deployment.md](docs/oracle-cloud-deployment.md) | Ubuntu VPS deployment guide |
| [docs/roadmap.md](docs/roadmap.md) | Project roadmap |

## Project Structure

```text
sales-crm-lite/
|-- config/                 Route configuration
|-- database/
|   |-- migrations/         Ordered SQL migrations
|   `-- seeders/            Local and demo seed data
|-- docs/                   Architecture, setup, deployment, screenshot notes
|-- public/                 Web root and compiled assets
|-- resources/css/          Tailwind source CSS
|-- src/
|   |-- Controllers/        MVC controllers
|   |-- Core/               Router, controller base, model base, auth, session, helpers
|   |-- Middleware/         Auth and CSRF middleware
|   |-- Models/             PDO model classes
|   |-- Services/           Small service helpers
|   `-- Views/              Layouts, partials, and module views
|-- storage/logs/           App logs
|-- tests/                  PHPUnit tests
|-- composer.json
|-- package.json
`-- tailwind.config.js
```

## Release And Version

Current portfolio release: `v1.0.0`.

Included in this release:

- MVP CRM workflow: leads, customers, follow-ups, conversion, dashboard.
- Admin user management and role-based scoping.
- Activity log, reporting foundation, and CSV exports.
- Tailwind UI foundation.
- Production readiness and Ubuntu VPS deployment documentation.
- PHPUnit test suite.

Planned future work:

- JavaScript charts for reports.
- More advanced permission/team workflows.
- Hosted demo environment.

Not currently included: SaaS tenants, dark mode, advanced charting, or payment/subscription features.

## License

This project declares the MIT license in [composer.json](composer.json).
