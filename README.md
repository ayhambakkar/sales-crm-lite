# Sales CRM Lite

> A lightweight, professional Customer Relationship Management system built from scratch with PHP 8, MySQL, and Tailwind CSS — no framework, no bloat.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/status-In_Development-orange?style=flat-square)

---

## Overview

Sales CRM Lite is a single-tenant CRM designed for small sales teams. It covers the full sales workflow: capturing leads, tracking pipeline stages, converting prospects into customers, and managing follow-up tasks — all within a clean, responsive interface.

The project is intentionally built **without a PHP framework** to demonstrate a deep understanding of how MVC architecture, routing, session management, and security work under the hood. It follows the same patterns used in Laravel, Symfony, and CodeIgniter — just implemented by hand.

---

## Features

### Phase 1 — MVP (Current)

- **Authentication** — Secure login/logout with bcrypt password hashing and session management
- **Role-based access control** — Admin and Sales Rep roles with scoped data visibility
- **Dashboard** — KPI cards, overdue follow-up alerts, and recent leads overview
- **Lead Management** — Full CRUD with pipeline status tracking (New → Contacted → Qualified → Won/Lost)
- **Lead Conversion** — One-click conversion from Won lead to Customer with field pre-population
- **Customer Management** — Full CRUD with linked follow-up history
- **Follow-Up Tasks** — Scheduled tasks tied to leads or customers with overdue detection
- **User Management** — Admin can create, edit, deactivate, and manage team accounts

### Phase 2 — In Roadmap

- **Activity Log** — Immutable audit trail of all CRM interactions
- **Reporting** — Conversion rates, leads-by-status charts, follow-up completion rates
- **Pagination** — All list views paginated above 20 records
- **Dark mode** — Optional theme toggle
- **CSV Export** — Export leads and customer reports

---

## Tech Stack

| Layer | Technology | Why |
|---|---|---|
| **Backend** | PHP 8.2+ | No framework — demonstrates core language mastery |
| **Database** | MySQL 8 / MariaDB 10.6+ | Relational data with full FK and transaction support |
| **CSS** | Tailwind CSS 3 | Utility-first, responsive-by-default, no custom CSS needed |
| **JavaScript** | Vanilla JS (ES2020+) | No framework overhead; raw DOM and Fetch API |
| **Architecture** | Custom MVC | Front controller, PSR-4 autoloading, PDO data layer |
| **Security** | PHP sessions + CSRF tokens | bcrypt, prepared statements, XSS escaping, CSP headers |
| **Dev tooling** | Composer, npm (Tailwind CLI) | Standard PHP dependency management |

---

## Prerequisites

- PHP 8.2 or higher
- MySQL 8.0+ or MariaDB 10.6+
- Composer
- Node.js + npm *(optional — only needed for Tailwind CSS CLI build)*

---

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/your-username/sales-crm-lite.git
cd sales-crm-lite
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure the environment

```bash
cp .env.example .env
```

Open `.env` and set your database credentials:

```env
APP_NAME="Sales CRM Lite"
APP_ENV=development
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sales_crm
DB_USER=your_db_user
DB_PASS=your_db_password
```

### 4. Set up the database

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run the schema
mysql -u root -p sales_crm < database/migrations/schema.sql

# Load demo data (optional)
mysql -u root -p sales_crm < database/seeders/seed.sql
```

### 5. Start the development server

```bash
php -S localhost:8000 -t public/
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

### Default Login Credentials (seed data)

| Name | Email | Password | Role |
|---|---|---|---|
| Admin User | admin@example.com | password | Admin |
| Alice Sales | alice@example.com | password | Sales Rep |
| Bob Sales | bob@example.com | password | Sales Rep |

> Change all passwords immediately after first login.

---

## Project Structure

```
sales-crm-lite/
│
├── public/                     ← Web root (the ONLY directory exposed to the web)
│   ├── index.php               ← Front controller — all requests enter here
│   └── assets/
│       ├── css/                ← Compiled Tailwind CSS
│       ├── js/                 ← Vanilla JavaScript modules
│       └── images/
│
├── src/                        ← Application source (not web-accessible)
│   ├── Core/                   ← Framework kernel (Router, Session, Auth, helpers)
│   ├── Controllers/            ← One controller per module
│   ├── Models/                 ← PDO-based data access layer
│   ├── Views/                  ← PHP template files
│   │   ├── layouts/            ← Shared page shells (app, guest)
│   │   ├── partials/           ← Reusable UI components (flash, pagination)
│   │   ├── dashboard/
│   │   ├── leads/
│   │   ├── customers/
│   │   ├── follow-ups/
│   │   ├── users/
│   │   ├── auth/
│   │   └── errors/
│   ├── Services/               ← Business logic layer (LeadService, etc.)
│   └── Middleware/             ← AuthMiddleware, CsrfMiddleware
│
├── config/                     ← App, database, and route configuration
├── database/
│   ├── migrations/             ← SQL schema files
│   └── seeders/                ← Demo data scripts
├── storage/
│   ├── logs/                   ← Application error logs (git-ignored)
│   └── uploads/                ← File uploads (git-ignored)
├── tests/
│   ├── Unit/
│   └── Integration/
├── docs/                       ← Full architecture documentation
├── .env.example                ← Environment template
├── composer.json
└── README.md
```

---

## Architecture

The application uses a **custom MVC implementation** with a front controller pattern:

```
Request → public/index.php → Router → Middleware → Controller → Model → View → Response
```

Key design decisions:

- **No framework** — Router, session management, CSRF protection, and templating are all hand-rolled
- **PSR-4 autoloading** — all classes follow `App\Layer\ClassName` namespacing
- **PDO + prepared statements** — zero raw SQL string interpolation; no ORM
- **Role-scoped queries** — every data query includes a user-ownership clause; no IDOR possible
- **Defense in depth** — auth enforced at router level AND at record level in every model

Full architecture documentation: [`docs/system-architecture.md`](docs/system-architecture.md)  
Security concept: [`docs/security-concept.md`](docs/security-concept.md)  
Database design + ERD: [`docs/database-design.md`](docs/database-design.md)

---

## Security Highlights

| Threat | Mitigation |
|---|---|
| SQL Injection | PDO prepared statements, `ATTR_EMULATE_PREPARES = false` |
| XSS | `htmlspecialchars()` on all output via `e()` helper + CSP header |
| CSRF | Synchronizer token pattern with `hash_equals()` comparison |
| IDOR | User-scoped WHERE clauses in every single-record query |
| Session Fixation | `session_regenerate_id(true)` on every login |
| Password Cracking | bcrypt with cost factor 12 |
| Clickjacking | `X-Frame-Options: DENY` header |

---

## Documentation

All architectural decisions are documented in the `docs/` folder:

| Document | Description |
|---|---|
| [`project-overview.md`](docs/project-overview.md) | Vision, goals, and key design decisions |
| [`requirements.md`](docs/requirements.md) | Functional and non-functional requirements with IDs |
| [`user-stories.md`](docs/user-stories.md) | 25 user stories with acceptance criteria |
| [`database-design.md`](docs/database-design.md) | ERD, table definitions, indexes, foreign keys |
| [`system-architecture.md`](docs/system-architecture.md) | MVC layers, request lifecycle, component design |
| [`security-concept.md`](docs/security-concept.md) | Auth flow, session hardening, OWASP mitigations |
| [`project-structure.md`](docs/project-structure.md) | Directory tree, naming conventions, web server config |
| [`roadmap.md`](docs/roadmap.md) | Milestones, effort estimates, branching strategy |

---

## Roadmap

```
Phase 1 — MVP (In Progress)       Phase 2 — Depth (Planned)
──────────────────────────        ──────────────────────────
[✓] Project architecture          [ ] Activities audit log
[ ] Authentication & sessions     [ ] Reporting & charts
[ ] User management (Admin)       [ ] Pagination
[ ] Lead pipeline (full CRUD)     [ ] CSV export
[ ] Customer management           [ ] Dark mode toggle
[ ] Follow-up tasks               [ ] Live demo deployment
[ ] Dashboard with KPIs
```

See the full milestone breakdown: [`docs/roadmap.md`](docs/roadmap.md)

---

## Running Tests

```bash
# All tests
composer test

# Unit tests only
composer test:unit

# Integration tests only
composer test:integration
```

---

## About This Project

This project was built as a **GitHub portfolio piece** to demonstrate senior-level full-stack PHP skills without relying on a framework. The focus areas are:

- **Architecture** — MVC from scratch, clean separation of concerns, no god classes
- **Security** — OWASP Top 10 mitigations implemented explicitly, not by convention
- **Database design** — normalized schema, proper FK constraints, index strategy
- **Code quality** — PSR-12 compliant, no framework magic, readable and maintainable
- **Documentation** — full ADR-style docs before any code — the way real teams work

If you're reviewing this for a job application or technical assessment, the `docs/` folder is the best place to start. It explains not just *what* was built, but *why* every decision was made.

---

## License

This project is licensed under the [MIT License](LICENSE).
