# Sales CRM Lite — Project Structure

## Overview

The project follows a standard MVC layout with a strict separation between the public web root and the application source. Only the `public/` directory is exposed to the web server. Everything else — controllers, models, views, config, and vendor libraries — sits outside the document root and is inaccessible via HTTP.

---

## Full Directory Tree

```
sales-crm-lite/                         ← Project root
│
├── public/                             ← Web server document root (ONLY this is public)
│   ├── index.php                       ← Front controller — single entry point
│   └── assets/
│       ├── css/
│       │   └── app.css                 ← Compiled Tailwind (production build)
│       ├── js/
│       │   ├── app.js                  ← Global JS (flash dismiss, modal init)
│       │   ├── confirm.js              ← Delete confirmation modal logic
│       │   └── charts.js              ← Phase 2: Chart.js wrappers
│       └── img/
│           └── logo.svg
│
├── src/                                ← Application source (not web-accessible)
│   │
│   ├── Core/                           ← Framework kernel
│   │   ├── Router.php                  ← HTTP routing + middleware runner
│   │   ├── Request.php                 ← Wraps $_GET, $_POST, $_SERVER
│   │   ├── Response.php                ← Redirect, JSON, abort helpers
│   │   ├── Session.php                 ← Session read/write/flash/destroy
│   │   ├── Auth.php                    ← Login state, user(), isAdmin()
│   │   ├── Controller.php              ← Base controller (render, redirect, abort, validate)
│   │   ├── Model.php                   ← Base model (PDO singleton, query helpers)
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php      ← Session validity + expiry check
│   │   │   └── CsrfMiddleware.php      ← Token generation and validation
│   │   └── helpers.php                 ← Global functions: e(), csrf_field(), url(), dd()
│   │
│   ├── Controllers/                    ← One controller per module
│   │   ├── AuthController.php          ← showLogin, login, logout
│   │   ├── DashboardController.php     ← index (KPIs + widgets)
│   │   ├── LeadController.php          ← index, create, store, show, edit, update, destroy, convert
│   │   ├── CustomerController.php      ← index, create, store, show, edit, update, destroy
│   │   ├── FollowUpController.php      ← index, create, store, edit, update, markDone, markCancelled, destroy
│   │   ├── UserController.php          ← index, create, store, edit, update, deactivate, destroy (Admin only)
│   │   ├── ActivityController.php      ← Phase 2: store (manual log)
│   │   └── ReportController.php        ← Phase 2: index, leadsReport, followUpReport
│   │
│   ├── Models/                         ← One model per DB table
│   │   ├── UserModel.php
│   │   ├── LeadModel.php
│   │   ├── CustomerModel.php
│   │   ├── FollowUpModel.php
│   │   ├── DashboardModel.php          ← Aggregation queries (no own table)
│   │   ├── ActivityModel.php           ← Phase 2
│   │   └── ReportModel.php             ← Phase 2
│   │
│   └── Views/                          ← PHP template files
│       │
│       ├── layouts/
│       │   ├── app.php                 ← Authenticated layout (nav, sidebar, footer)
│       │   └── guest.php               ← Minimal layout (login page)
│       │
│       ├── partials/
│       │   ├── flash.php               ← Success/error/warning banner
│       │   ├── pagination.php          ← Page navigation strip
│       │   └── confirm-modal.php       ← Delete confirmation dialog
│       │
│       ├── auth/
│       │   └── login.php               ← Login form
│       │
│       ├── dashboard/
│       │   └── index.php               ← KPI cards + widgets
│       │
│       ├── leads/
│       │   ├── index.php               ← Lead list + filters
│       │   ├── create.php              ← New lead form
│       │   ├── show.php                ← Lead detail view
│       │   └── edit.php                ← Edit lead form
│       │
│       ├── customers/
│       │   ├── index.php
│       │   ├── create.php
│       │   ├── show.php                ← Includes linked follow-ups panel
│       │   └── edit.php
│       │
│       ├── follow-ups/
│       │   ├── index.php               ← Follow-up list + filters
│       │   ├── create.php
│       │   └── edit.php
│       │
│       ├── users/                      ← Admin only
│       │   ├── index.php
│       │   ├── create.php
│       │   └── edit.php
│       │
│       ├── reports/                    ← Phase 2, Admin only
│       │   └── index.php
│       │
│       └── errors/
│           ├── 403.php
│           ├── 404.php
│           └── 500.php
│
├── config/                             ← Application configuration
│   ├── app.php                         ← App name, URL, timezone, session lifetime
│   ├── database.php                    ← PDO DSN built from .env values
│   └── routes.php                      ← All route definitions in one place
│
├── database/                           ← Database source files
│   ├── schema.sql                      ← Full CREATE TABLE statements (committed)
│   └── seed.sql                        ← Demo data for local development (committed)
│
├── storage/                            ← Runtime-generated files (git-ignored)
│   └── logs/
│       └── error.log                   ← Application error log (production)
│
├── docs/                               ← Project documentation
│   ├── project-overview.md
│   ├── requirements.md
│   ├── user-stories.md
│   ├── roadmap.md
│   ├── database-design.md
│   ├── system-architecture.md
│   ├── security-concept.md
│   └── project-structure.md            ← This file
│
├── tests/                              ← Test files (stretch goal)
│   ├── Unit/
│   │   ├── AuthTest.php
│   │   ├── LeadModelTest.php
│   │   └── CsrfMiddlewareTest.php
│   └── Integration/
│       └── LeadCrudTest.php
│
├── .env                                ← Local secrets (git-ignored)
├── .env.example                        ← Template (committed)
├── .gitignore
├── composer.json                       ← Autoloading config (PSR-4)
├── composer.lock
├── tailwind.config.js                  ← Tailwind CSS config (CLI build)
├── package.json                        ← Tailwind dev dependency
└── README.md                           ← Setup instructions + feature overview
```

---

## Key Files Explained

### `public/index.php` — Front Controller

The web server is configured to route all requests here. It:
1. Defines `APP_ROOT` (the project root, one level up from `public/`)
2. Requires the Composer autoloader
3. Loads `.env` and all config files
4. Sets security HTTP headers
5. Starts the PHP session with hardened settings
6. Instantiates and dispatches the `Router`

```php
// Nginx config (simplified)
// try_files $uri $uri/ /index.php?$query_string;

// Apache .htaccess (in public/)
// RewriteEngine On
// RewriteCond %{REQUEST_FILENAME} !-f
// RewriteRule ^ index.php [L]
```

### `config/routes.php` — Route Definitions

All routes live in one file for easy overview. Format:

```php
return [
    ['GET',  '/',                        'DashboardController', 'index',   ['auth']],
    ['GET',  '/leads',                   'LeadController',      'index',   ['auth']],
    ['GET',  '/leads/create',            'LeadController',      'create',  ['auth']],
    ['POST', '/leads',                   'LeadController',      'store',   ['auth', 'csrf']],
    ['GET',  '/leads/{id}',              'LeadController',      'show',    ['auth']],
    ['GET',  '/leads/{id}/edit',         'LeadController',      'edit',    ['auth']],
    ['POST', '/leads/{id}/edit',         'LeadController',      'update',  ['auth', 'csrf']],
    ['POST', '/leads/{id}/delete',       'LeadController',      'destroy', ['auth', 'csrf']],
    ['POST', '/leads/{id}/convert',      'LeadController',      'convert', ['auth', 'csrf']],
    // ...
];
```

### `src/Core/helpers.php` — Global Utilities

```php
e(mixed $val): string           // htmlspecialchars wrapper — use on all output
csrf_field(): string            // renders <input type="hidden" name="csrf_token" ...>
url(string $path): string       // prepends APP_URL
old(string $key, $default)      // repopulates form field after validation failure
dd(mixed ...$vars): never       // dump-and-die for debugging (remove before deploy)
```

### `composer.json` — Autoloading

No framework packages. Composer is used solely for PSR-4 autoloading:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        },
        "files": [
            "src/Core/helpers.php"
        ]
    }
}
```

Class name → file path mapping:
```
App\Controllers\LeadController   →  src/Controllers/LeadController.php
App\Models\LeadModel             →  src/Models/LeadModel.php
App\Core\Router                  →  src/Core/Router.php
```

### `.gitignore`

```
# Environment & secrets
.env
.env.*.local

# Runtime storage
storage/logs/
storage/cache/

# Vendor
vendor/

# Node / Tailwind build
node_modules/

# OS
.DS_Store
Thumbs.db

# IDE
.idea/
.vscode/
*.swp
```

---

## View Template Structure

Views are plain PHP files. They never contain business logic. The rendering contract:

```
layouts/app.php receives:
    $content  (string)  ← rendered inner view
    $title    (string)  ← page <title>
    $user     (array)   ← current user (from Auth::user())

All other views receive:
    their own named variables (passed via Controller::render())
```

**Partial usage in views:**
```php
// In any view file:
<?php include APP_ROOT . '/src/Views/partials/flash.php'; ?>
```

**Layout slot pattern:**
```php
// layouts/app.php
<main>
    <?= $content ?>   ← injected by Controller::render()
</main>
```

---

## Naming Reference

| Type | Convention | Example |
|---|---|---|
| PHP namespace | `App\Layer` | `App\Controllers`, `App\Models`, `App\Core` |
| PHP class | PascalCase | `LeadController`, `UserModel`, `AuthMiddleware` |
| PHP method | camelCase | `findAllScoped()`, `markDone()` |
| PHP file | Matches class name | `LeadController.php` |
| View directory | kebab-case | `follow-ups/`, `leads/` |
| View file | lowercase | `index.php`, `create.php`, `edit.php`, `show.php` |
| Route URI | kebab-case | `/follow-ups`, `/leads/{id}/edit` |
| DB table | snake_case, plural | `follow_ups`, `users`, `leads` |
| DB column | snake_case | `assigned_user_id`, `created_at` |
| JS file | kebab-case | `confirm.js`, `charts.js` |
| CSS class | Tailwind utility | (no custom class names in v1) |
| Git branch | kebab-case with prefix | `feature/auth`, `feature/leads`, `fix/session-expiry` |

---

## Web Server Configuration

### Development (PHP built-in server)

```bash
# Serve from project root; route everything through public/index.php
php -S localhost:8000 -t public/
```

### Apache (production)

`public/.htaccess`:
```apache
Options -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

Document root must be set to `.../sales-crm-lite/public/`.

### Nginx (production)

```nginx
server {
    root /var/www/sales-crm-lite/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\. {
        deny all;          # block access to .env, .git, etc.
    }
}
```

---

## Environment Setup

### First-time setup

```bash
# 1. Clone the repository
git clone https://github.com/your-username/sales-crm-lite.git
cd sales-crm-lite

# 2. Install Composer dependencies
composer install

# 3. Configure environment
cp .env.example .env
# → Edit .env: set DB_USER, DB_PASS, DB_NAME

# 4. Create database and run schema
mysql -u root -p -e "CREATE DATABASE sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p sales_crm < database/schema.sql
mysql -u root -p sales_crm < database/seed.sql

# 5. (Optional) Build Tailwind CSS
npm install
npx tailwindcss -i ./src/css/input.css -o ./public/assets/css/app.css --watch

# 6. Start development server
php -S localhost:8000 -t public/
```

### Seed Data (default credentials)

| Name | Email | Password | Role |
|---|---|---|---|
| Admin User | admin@example.com | password | admin |
| Alice Sales | alice@example.com | password | sales_rep |
| Bob Sales | bob@example.com | password | sales_rep |

> Seed passwords are plain-text in `seed.sql` and are hashed by the seed script. Change all passwords after first login.
