# Architecture Overview

Sales CRM Lite is a custom PHP 8.2 MVC application without a framework. The codebase is intentionally small and explicit so the request lifecycle, security checks, data access, and rendering flow are easy to inspect.

## Request Lifecycle

```text
Browser
  -> public/index.php
  -> Config + security headers + Session
  -> config/routes.php
  -> App\Core\Router
  -> Middleware
  -> Controller
  -> Model / Service
  -> View + Layout
  -> Response
```

## Front Controller

[public/index.php](../public/index.php) is the only PHP entry point exposed by the web server. It defines `APP_ROOT`, loads Composer autoloading, loads `.env` configuration, registers production-safe exception handling, sends global security headers, starts the session, loads routes, and dispatches the router.

The deployment configuration must point Nginx or Apache to `public/` so application source, migrations, logs, and environment files are not web-accessible.

## Router

[src/Core/Router.php](../src/Core/Router.php) is a lightweight custom router. It supports `GET` and `POST` route registration, URI placeholders such as `/leads/{id}`, middleware tags, and controller dispatch.

Routes are defined in [config/routes.php](../config/routes.php). Middleware tags currently include:

- `auth` for authenticated access.
- `csrf` for POST request protection.
- `admin` for admin-only routes.

## Controllers

Controllers in [src/Controllers](../src/Controllers) handle HTTP-facing application flow:

- Read request input from query strings, route parameters, or POST data.
- Coordinate validation and authorization checks.
- Call models or services for persistence and cross-cutting behavior.
- Set flash messages and redirects.
- Render PHP views.

Controllers stay close to the current MVC structure and avoid embedding broad database logic directly in request handlers.

## Models

Models in [src/Models](../src/Models) own data access. They extend [src/Core/Model.php](../src/Core/Model.php), which wraps PDO helpers for prepared queries, single-row lookup, multi-row lookup, command execution, and transactions where needed.

Model responsibilities include:

- Validation helpers for statuses, priorities, roles, and filters.
- Scoped query helpers for admin vs. sales rep access.
- CRUD operations for users, leads, customers, follow-ups, activities, reports, and exports.
- Data aggregation for dashboard and report views.

SQL injection risk is reduced by using prepared statements and parameter binding rather than string-concatenated user input.

## Views

Views in [src/Views](../src/Views) are plain PHP templates. The app uses shared layouts and partials rather than a template engine.

Important layout files:

- `layouts/app.php` for authenticated CRM pages.
- `layouts/guest.php` for login.
- `partials/flash.php` for flash messages.
- `partials/pagination.php` for reusable pagination.
- Badge and list partials for repeated UI states.

Views should receive already-scoped data from controllers/models and should not contain database queries.

## Middleware

Middleware in [src/Middleware](../src/Middleware) protects routes before controller actions run.

[AuthMiddleware](../src/Middleware/AuthMiddleware.php) verifies that a user is logged in before protected routes are served.

[CsrfMiddleware](../src/Middleware/CsrfMiddleware.php) validates POST request tokens to protect state-changing actions.

Admin-only enforcement is handled through the router's `admin` middleware tag and `Auth::isAdmin()`.

## Services

Services are intentionally limited. [src/Services/ActivityLogger.php](../src/Services/ActivityLogger.php) is the main service helper and centralizes audit logging calls for users, leads, customers, follow-ups, auth, and system actions.

This keeps controllers from duplicating activity log insert details while preserving the custom MVC architecture.

## Activity Log

The activity log stores immutable audit events for important actions:

- Auth events such as login and logout.
- User events such as create, update, activate, deactivate, and password reset.
- Lead, customer, and follow-up create/update/delete events.
- Lead conversion events.
- Report views and CSV exports.

Activities include entity type, entity ID, action, description, optional metadata, actor user, and timestamp. Sensitive data such as password hashes is not logged.

## Auth And Role Scoping

Authentication state is stored in the PHP session using [src/Core/Auth.php](../src/Core/Auth.php) and [src/Core/Session.php](../src/Core/Session.php). Session IDs regenerate on login, logout destroys the server-side session, and production cookies are Secure, HttpOnly, and SameSite=Strict.

The app has two roles:

- `admin`: can manage users and access global CRM records.
- `sales_rep`: can access assigned leads, customers, and follow-ups where applicable.

Scoping is enforced server-side in controller/model logic. UI navigation hides admin-only links for sales reps, but the security boundary is not only visual.

## Production Boundary

Production readiness is handled through:

- Generic production error pages with file logging.
- Security headers in the front controller.
- `.env` configuration outside version control.
- Nginx/PHP-FPM deployment with `public/` as the web root.
- Writable `storage/logs` for application logs.
- HTTPS-required production guidance.
