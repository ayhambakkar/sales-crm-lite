# Production Environment Example

Copy the values below into `.env` on the production server and replace every placeholder. Do not commit the real `.env` file.

```env
# -----------------------------------------------------------------------------
# Application
# -----------------------------------------------------------------------------
APP_NAME="Sales CRM Lite"
APP_ENV=production
APP_URL=https://crm.example.com
APP_DEBUG=false

# -----------------------------------------------------------------------------
# Database
# -----------------------------------------------------------------------------
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_crm
DB_USERNAME=sales_crm_app
DB_PASSWORD=REPLACE_WITH_STRONG_RANDOM_PASSWORD

# -----------------------------------------------------------------------------
# Session
# -----------------------------------------------------------------------------
SESSION_LIFETIME=7200
SESSION_NAME=sales_crm_session

# -----------------------------------------------------------------------------
# Security
# -----------------------------------------------------------------------------
APP_TIMEZONE=UTC
```

## Notes

- `APP_ENV=production` enables production session cookie behavior and production-only HSTS.
- `APP_DEBUG=false` keeps raw exceptions out of browser responses.
- Application errors are logged to `storage/logs/app.log`.
- `APP_URL` must use `https://` in production.
- `DB_PASSWORD` must be a strong unique password, not the placeholder above.
- Keep `.env` outside version control and restrict permissions with `chmod 640 .env`.
