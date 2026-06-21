# Sales CRM Lite

> A lightweight, single-tenant CRM for small sales teams, built with custom PHP 8.2 MVC, MySQL, and Tailwind CSS.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![Status](https://img.shields.io/badge/status-MVP-green?style=flat-square)

## Project Overview

Sales CRM Lite is a portfolio-grade CRM application that demonstrates how to build a secure, maintainable PHP application without a framework. It uses a front controller, custom router, middleware, controllers, PDO models, PHP views, and Tailwind CSS.

The current MVP supports authentication, admin user management, leads, customers, lead-to-customer conversion, follow-up tasks, and a scoped CRM dashboard. It is intentionally single-tenant for now.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.2+ |
| Architecture | Custom MVC with PSR-4 autoloading |
| Database | MySQL 8.0+ or MariaDB 10.6+ |
| Data access | PDO prepared statements |
| Frontend | PHP views with Tailwind CSS |
| Tooling | Composer, PHPUnit, npm, Tailwind CLI |

## Implemented Phase 1 Features

- Authentication with login, logout, bcrypt password hashes, CSRF protection, session regeneration, and hardened logout.
- Login throttling with database-backed failed login tracking.
- Admin-only user management with create, edit, activate, deactivate, and password reset actions.
- Role-based access for `admin` and `sales_rep` users.
- Leads module with CRUD, search, filters, sorting, pagination, assignment scoping, status, priority, and estimated value.
- Lead-to-customer conversion with transaction-safe customer creation and conversion tracking.
- Customers module with CRUD, search, filters, sorting, pagination, and assignment scoping.
- Follow-Up Tasks module for leads and customers with open, done, cancelled, overdue, priority, due date, search, filters, sorting, and pagination.
- Dashboard KPIs for leads, customers, pipeline value, conversion rate, overdue follow-ups, due-today follow-ups, recent records, and upcoming tasks.
- Activity log and audit trail for important auth, user, lead, customer, conversion, and follow-up actions.
- Tailwind-based SaaS UI foundation with app layout, guest layout, navigation, tables, forms, flash messages, badges, and pagination.
- PHPUnit coverage for the foundation, auth hardening, user management, leads, customers, conversion, dashboard, and follow-ups.

## Role-Based Access

- Admin users can manage users and can view, assign, edit, delete, and convert all CRM records.
- Sales reps can view and manage only leads, customers, and follow-ups assigned to them.
- The Users navigation item is visible only to admins.
- Record scoping is enforced in controller/model logic, not only in the UI.

## Phase 2 Roadmap

- Reporting views and charts.
- CSV export for CRM lists and reports.
- More advanced permissions and team workflows.
- Production deployment hardening and hosted demo.

The app does not currently include SaaS tenants, reporting, CSV export, or dark mode.

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or MariaDB 10.6+
- Node.js and npm

## Fresh-Install Checklist

1. `composer install`
2. `npm install`
3. `cp .env.example .env`
4. Run migrations in order.
5. Run seeders.
6. `npm run build:css`
7. `composer test`
8. `php -S localhost:8000 -t public`

## Installation

Install PHP and frontend dependencies:

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

Add `-p` to the MySQL commands below if your local MySQL root user requires a password.

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
```

Run the local admin seeder:

```bash
mysql -u root sales_crm < database/seeders/001_seed_admin_user.sql
```

Load demo CRM data:

```bash
mysql -u root sales_crm < database/seeders/002_seed_demo_crm_data.sql
```

## Demo Login Credentials

Both demo accounts use the local development password `password`.

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@example.com | password |
| Sales Rep | sales@example.com | password |

Change these credentials before using the app outside local development.

## CSS Build

Build the production stylesheet:

```bash
npm run build:css
```

Run the Tailwind watcher while editing UI files:

```bash
npm run dev:css
```

The compiled stylesheet is written to `public/assets/css/app.css`.

## Local Server

Start the PHP development server:

```bash
php -S localhost:8000 -t public
```

Open [http://localhost:8000](http://localhost:8000).

## Tests

Run the test suite:

```bash
composer test
```

Additional scripts are available:

```bash
composer test:unit
composer test:integration
```

## Project Structure

```text
sales-crm-lite/
|-- config/                 Route configuration
|-- database/
|   |-- migrations/         Ordered SQL migrations
|   `-- seeders/            Local and demo seed data
|-- public/                 Web root and compiled assets
|-- resources/css/          Tailwind source CSS
|-- src/
|   |-- Controllers/        MVC controllers
|   |-- Core/               Router, controller base, model base, auth, session, helpers
|   |-- Middleware/         Auth and CSRF middleware
|   |-- Models/             PDO model classes
|   `-- Views/              Layouts, partials, and module views
|-- storage/logs/           App logs
|-- tests/                  PHPUnit tests
|-- composer.json
|-- package.json
`-- tailwind.config.js
```

## Security Notes

- Passwords are stored with bcrypt hashes.
- SQL access uses PDO prepared statements.
- POST routes use CSRF middleware.
- Login throttling locks repeated failed attempts for 15 minutes.
- Authenticated sessions are regenerated on login and destroyed on logout.
- Production-safe logging writes to `storage/logs/app.log`.

## License

This project declares the MIT license in `composer.json`.
