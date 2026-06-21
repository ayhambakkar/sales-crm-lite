# Production Checklist

This checklist keeps Sales CRM Lite deployable without changing its custom PHP MVC architecture.

## Local Setup

1. Install dependencies: `composer install` and `npm install`.
2. Copy configuration: `cp .env.example .env`.
3. Create the local database:
   `mysql -u root -e "CREATE DATABASE IF NOT EXISTS sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`
4. Run migrations in order.
5. Seed local accounts and optional demo CRM data.
6. Build CSS: `npm run build:css`.
7. Run tests: `composer test`.
8. Start the local server: `php -S localhost:8000 -t public`.

## Production Setup

1. Point the web server document root to `public/`.
2. Create a production `.env` with `APP_ENV=production`, `APP_DEBUG=false`, the production `APP_URL`, and production database credentials.
3. Require HTTPS. Production session cookies are marked Secure, and HSTS is sent only in production.
4. Install PHP dependencies without dev packages: `composer install --no-dev --optimize-autoloader`.
5. Install frontend dependencies and build the stylesheet: `npm install` then `npm run build:css`.
6. Ensure `storage/logs` is writable by the PHP/web server user.
7. Run migrations in order against the production database.
8. Seed only intentional production users. Do not load demo CRM data into production.
9. Verify `/health` returns an OK JSON response without exposing configuration values.

## Migration Order

```bash
mysql -u root sales_crm < database/migrations/001_create_users_table.sql
mysql -u root sales_crm < database/migrations/002_create_failed_logins_table.sql
mysql -u root sales_crm < database/migrations/003_create_leads_table.sql
mysql -u root sales_crm < database/migrations/004_create_customers_table.sql
mysql -u root sales_crm < database/migrations/005_add_lead_conversion_fields.sql
mysql -u root sales_crm < database/migrations/006_create_follow_ups_table.sql
mysql -u root sales_crm < database/migrations/007_create_activities_table.sql
```

## Seed Data

Use the admin seeder when you need a local or initial admin account:

```bash
mysql -u root sales_crm < database/seeders/001_seed_admin_user.sql
```

The demo CRM seeder is for local demos and portfolio screenshots only:

```bash
mysql -u root sales_crm < database/seeders/002_seed_demo_crm_data.sql
```

Do not run the demo seeder in production unless the database is explicitly disposable.

## Tests And CSS

Run the PHP test suite:

```bash
composer test
```

Build the compiled Tailwind stylesheet:

```bash
npm run build:css
```

The generated file `public/assets/css/app.css` is ignored by Git and should be created during local setup or deployment.

## Troubleshooting

- Blank or unstyled pages: run `npm run build:css` and confirm `public/assets/css/app.css` exists.
- Generic 500 page in production: check `storage/logs/app.log` and confirm `storage/logs` is writable.
- Login issues behind HTTPS: confirm `APP_ENV=production`, `APP_URL` uses `https://`, and the request is served over HTTPS.
- Database errors: confirm migration order, database credentials, and that the database user can read and write tables.
- Missing dependencies: run `composer install --no-dev --optimize-autoloader` on production and `composer install` locally.
