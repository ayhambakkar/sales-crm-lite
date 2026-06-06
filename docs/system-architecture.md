# Sales CRM Lite — System Architecture

## Architectural Style

Sales CRM Lite follows the **Model-View-Controller (MVC)** pattern implemented from scratch in PHP 8 — no framework. This approach demonstrates a deep understanding of how web frameworks work internally, which is the goal of a portfolio project at this level.

The application uses a **front controller** pattern: every HTTP request enters through a single entry point (`public/index.php`), which delegates to a `Router`, which dispatches to the appropriate `Controller`.

---

## Layer Responsibilities

```
┌─────────────────────────────────────────────────────────────┐
│                        Browser                              │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP Request
┌──────────────────────────▼──────────────────────────────────┐
│                    public/index.php                         │
│              Front Controller (entry point)                 │
│  • Bootstraps autoloader and config                         │
│  • Starts session                                           │
│  • Hands off to Router                                      │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                        Router                               │
│                  src/Core/Router.php                        │
│  • Matches URI + HTTP method to a route definition          │
│  • Runs Auth middleware before dispatching                  │
│  • Calls Controller::method()                               │
└──────────────┬──────────────────────────┬───────────────────┘
               │                          │
    Auth fails │                 Route matched
               ▼                          ▼
         Redirect                ┌────────────────┐
         /login                  │   Controller   │
                                 │  src/Controllers│
                                 │                │
                                 │ • Reads input  │
                                 │ • Validates    │
                                 │ • Calls Model  │
                                 │ • Renders View │
                                 └────┬──────┬────┘
                                      │      │
                             ┌────────▼─┐  ┌─▼───────┐
                             │  Model   │  │  View   │
                             │ src/Models│  │src/Views│
                             │          │  │         │
                             │ PDO layer│  │PHP tmpl │
                             │ SQL only │  │ + layout│
                             └────┬─────┘  └─────────┘
                                  │
                          ┌───────▼───────┐
                          │    Database   │
                          │  MySQL/MariaDB│
                          └───────────────┘
```

---

## Core Components

### Front Controller — `public/index.php`

The sole entry point for all requests. Responsibilities:
1. Define the application root constant (`APP_ROOT`)
2. Require the Composer autoloader (or manual PSR-4 loader)
3. Load `.env` configuration via `Config::load()`
4. Initialize the PHP session (`session_start()`)
5. Instantiate and invoke `Router::dispatch()`

Nothing else lives in `public/` except static assets (`css/`, `js/`, `img/`).

---

### Router — `src/Core/Router.php`

A lightweight HTTP router supporting `GET` and `POST` verbs. Routes are registered as an array of definitions:

```
[METHOD, URI pattern, Controller class, method, middleware[]]
```

**Example route table (conceptual):**
```
GET    /                    DashboardController  index    [auth]
GET    /leads               LeadController       index    [auth]
POST   /leads               LeadController       store    [auth, csrf]
GET    /leads/{id}          LeadController       show     [auth]
GET    /leads/{id}/edit     LeadController       edit     [auth]
POST   /leads/{id}/edit     LeadController       update   [auth, csrf]
POST   /leads/{id}/delete   LeadController       destroy  [auth, csrf]
GET    /login               AuthController       showLogin
POST   /login               AuthController       login    [csrf]
POST   /logout              AuthController       logout   [auth, csrf]
```

URI parameters (e.g. `{id}`) are extracted via regex and passed to the controller method as arguments.

**Middleware pipeline:** Before dispatching, the router runs configured middleware in order:
1. `AuthMiddleware` — checks session validity; redirects to `/login` if not authenticated
2. `CsrfMiddleware` — validates CSRF token on POST requests; returns 403 on failure

---

### Base Controller — `src/Core/Controller.php`

All controllers extend this base class. Provides:
- `render(string $view, array $data)` — loads a view template within the shared layout
- `redirect(string $url)` — issues a 302 redirect with `exit`
- `currentUser()` — returns the authenticated user from session
- `isAdmin()` — boolean role check
- `abort(int $code)` — renders an error page (404, 403, 500)
- `validate(array $rules, array $data): array` — basic server-side validation

---

### Base Model — `src/Core/Model.php`

All models extend this base class. Provides:
- `db(): PDO` — returns the singleton PDO connection
- `query(string $sql, array $params): PDOStatement` — prepares and executes a statement
- `findAll(string $sql, array $params): array` — returns array of associative arrays
- `findOne(string $sql, array $params): ?array` — returns one row or null
- `execute(string $sql, array $params): bool` — for INSERT/UPDATE/DELETE
- `lastInsertId(): int` — wraps PDO lastInsertId

All queries go through these methods — raw PDO is never called directly in a controller.

---

### Application Controllers

| Controller | File | Responsibilities |
|---|---|---|
| `AuthController` | Controllers/AuthController.php | Login, logout, session init |
| `DashboardController` | Controllers/DashboardController.php | KPI aggregation, widget data |
| `LeadController` | Controllers/LeadController.php | Full CRUD + status update + conversion |
| `CustomerController` | Controllers/CustomerController.php | Full CRUD |
| `FollowUpController` | Controllers/FollowUpController.php | Full CRUD + mark done/cancelled |
| `UserController` | Controllers/UserController.php | Admin: user CRUD + deactivate |
| `ActivityController` | Controllers/ActivityController.php | *(Phase 2)* log creation |
| `ReportController` | Controllers/ReportController.php | *(Phase 2)* analytics queries |

---

### Application Models

| Model | File | Key Methods |
|---|---|---|
| `UserModel` | Models/UserModel.php | `findByEmail`, `create`, `update`, `deactivate` |
| `LeadModel` | Models/LeadModel.php | `findAllScoped`, `findById`, `create`, `updateStatus`, `convertToCustomer` |
| `CustomerModel` | Models/CustomerModel.php | `findAllScoped`, `findById`, `create`, `createFromLead` |
| `FollowUpModel` | Models/FollowUpModel.php | `findAllScoped`, `findOverdue`, `markDone`, `markCancelled` |
| `ActivityModel` | Models/ActivityModel.php | *(Phase 2)* `log`, `findByLead`, `findByCustomer` |
| `DashboardModel` | Models/DashboardModel.php | `getKpis`, `getOverdueFollowUps`, `getRecentLeads` |

---

### View System — `src/Views/`

Views are plain PHP template files. No templating language (no Twig/Blade) — the goal is to show raw PHP competence.

**Layout rendering flow:**
```
Controller::render('leads/index', $data)
  → loads src/Views/layouts/app.php
    → app.php includes src/Views/leads/index.php at the {{content}} slot
      → index.php uses $data variables directly
```

**Shared partials:**
- `layouts/app.php` — main HTML shell (nav, sidebar, footer)
- `layouts/guest.php` — minimal layout for login page
- `partials/flash.php` — flash message banner
- `partials/pagination.php` — reusable pagination strip
- `partials/confirm-modal.php` — delete confirmation dialog (JS-driven)

---

### Session & Auth — `src/Core/`

| Class | Responsibility |
|---|---|
| `Session` | Wrapper around `$_SESSION`: get, set, flash, destroy |
| `Auth` | `check()`, `user()`, `login(array $user)`, `logout()`, `isAdmin()` |
| `AuthMiddleware` | Intercepts requests, validates session, enforces expiry |
| `CsrfMiddleware` | Generates tokens (on GET), validates tokens (on POST) |

---

### Config — `config/`

| File | Purpose |
|---|---|
| `config/app.php` | App name, base URL, timezone, session lifetime |
| `config/database.php` | PDO DSN, credentials — reads from `.env` |
| `.env` | Actual secrets (git-ignored) |
| `.env.example` | Template committed to repo |

---

## Request Lifecycle (Detailed)

```
1. Browser sends: GET /leads?status=new

2. public/index.php
   ├── require autoloader
   ├── Config::load('.env')
   ├── session_start()
   └── (new Router())->dispatch()

3. Router::dispatch()
   ├── Parse $_SERVER['REQUEST_URI'] → path = '/leads', query = ['status' => 'new']
   ├── Match route: GET /leads → LeadController::index
   ├── Run AuthMiddleware::handle()
   │   ├── Session valid? Yes → continue
   │   └── Session expired? → Session::flash('error', 'Session expired') + redirect /login
   └── Call LeadController::index()

4. LeadController::index()
   ├── $status = $_GET['status'] ?? ''   (sanitized)
   ├── $q      = $_GET['q'] ?? ''        (sanitized)
   ├── $user   = Auth::user()
   ├── $leads  = LeadModel::findAllScoped($user, $status, $q)
   └── $this->render('leads/index', compact('leads', 'status', 'q'))

5. Controller::render('leads/index', $data)
   ├── extract($data) into scope
   ├── ob_start()
   ├── include 'src/Views/leads/index.php'   → captures content
   ├── $content = ob_get_clean()
   └── include 'src/Views/layouts/app.php'   → echoes full page

6. Browser receives HTML response.
```

---

## Design Patterns

| Pattern | Where Used | Purpose |
|---|---|---|
| Front Controller | `public/index.php` | Single entry point, centralised bootstrapping |
| MVC | Entire application | Separation of concerns |
| Repository (light) | Model classes | Isolate all SQL from controllers |
| Singleton | PDO connection | One DB connection per request |
| Middleware chain | Router | Auth + CSRF checks before dispatch |
| Template Method | Base Controller/Model | Shared behaviour with extension points |
| Active Record (light) | Models | Data + query logic co-located per entity |

---

## Naming Conventions

| Artifact | Convention | Example |
|---|---|---|
| PHP classes | PascalCase | `LeadController`, `UserModel` |
| PHP methods | camelCase | `findAllScoped`, `markDone` |
| PHP files | PascalCase (matches class) | `LeadController.php` |
| DB tables | snake_case, plural | `follow_ups`, `users` |
| DB columns | snake_case | `assigned_user_id`, `created_at` |
| URL routes | kebab-case | `/follow-ups`, `/leads/{id}/edit` |
| View files | kebab-case | `leads/index.php`, `leads/create.php` |
| CSS classes | Tailwind utility | `bg-blue-600 text-white px-4 py-2` |
| JS variables | camelCase | `confirmDelete`, `handleStatusChange` |

---

## Technology Integration

### Tailwind CSS

Used via CDN in development. For production the CLI build is used to purge unused classes, reducing stylesheet size to ~10KB.

```html
<!-- Development -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Production (generated file) -->
<link rel="stylesheet" href="/assets/css/app.css">
```

### Vanilla JavaScript

No build step required. All JS is written in plain ES2020 and lives in `public/assets/js/`. Responsibilities:
- Confirm-before-delete modals
- Inline status dropdown updates (fetch POST + partial DOM update)
- Form validation feedback
- Flash message auto-dismiss timer
- *(Phase 2)* Chart rendering with Chart.js

### PDO (PHP Data Objects)

The database layer uses PDO exclusively with:
- `PDO::ATTR_ERRMODE` → `PDO::ERRMODE_EXCEPTION` (throw on error)
- `PDO::ATTR_DEFAULT_FETCH_MODE` → `PDO::FETCH_ASSOC`
- `PDO::ATTR_EMULATE_PREPARES` → `false` (real prepared statements)

---

## Error Handling

| Scenario | Behaviour |
|---|---|
| 404 Not Found | Custom 404 view rendered via `Router::notFound()` |
| 403 Forbidden | Rendered via `Controller::abort(403)` when role check fails |
| 500 Server Error | In development: full exception with stack trace. In production: generic error page; exception logged to file |
| DB connection failure | Caught in `config/database.php`; application aborts with 500 |
| Form validation failure | Controller re-renders the form with error messages; input is repopulated |

---

## Scalability Notes

This application is intentionally not over-engineered. However, the architecture supports growth:

- Adding a new module requires: one Controller, one Model, one set of Views, and route entries — no other files to touch
- Role system can be extended to a third role (e.g. Manager) by adding it to the `users.role` ENUM and updating `Auth::isAdmin()` to `Auth::hasRole(string $role)`
- The Model base class can be replaced with a proper ORM (Eloquent, Doctrine) without changing controller code
- REST API endpoints could be added as a separate route group returning JSON responses
