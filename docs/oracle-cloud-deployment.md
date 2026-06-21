# Oracle Cloud Ubuntu VPS Deployment

This guide deploys Sales CRM Lite on an Ubuntu VPS using Nginx, PHP-FPM, MySQL or MariaDB, and HTTPS. It uses placeholders only; replace domains, paths, users, and passwords for your server.

The app is a custom PHP MVC project. The web root must point to `public/`, not to the repository root.

## Assumptions

- Ubuntu 22.04 LTS or 24.04 LTS.
- Domain: `crm.example.com`.
- App path: `/var/www/sales-crm-lite`.
- Linux deploy user: `deploy`.
- Web server user: `www-data`.
- PHP-FPM socket: `/run/php/php8.2-fpm.sock` or the socket for your installed PHP version.
- Database name: `sales_crm`.
- Database user: `sales_crm_app`.

## 1. Oracle Cloud Network Setup

In Oracle Cloud Infrastructure, make sure the instance can receive web traffic:

1. Reserve or note the instance public IP address.
2. Point your domain DNS `A` record to the public IP.
3. In the instance VCN security list or network security group, allow inbound TCP traffic:
   - SSH: `22` from your trusted IP range.
   - HTTP: `80` from `0.0.0.0/0`.
   - HTTPS: `443` from `0.0.0.0/0`.
4. On Ubuntu, allow the same services in UFW:

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

## 2. Ubuntu Server Setup

Update the server and install system packages:

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y nginx mysql-server git unzip curl ca-certificates nodejs npm
```

If you prefer MariaDB:

```bash
sudo apt install -y mariadb-server
```

Install PHP-FPM and required PHP extensions:

```bash
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-intl
```

If your Ubuntu release uses a newer PHP package by default, replace `php8.2-*` with that installed version, such as `php8.3-*`. The app requires PHP 8.2 or newer.

Required runtime extensions:

- `pdo_mysql` / `mysqli` package via `php8.2-mysql`
- `mbstring`
- `session`
- `json`
- `filter`
- `openssl`

Helpful deployment/tooling extensions:

- `curl`
- `zip`
- `xml`
- `intl`

Check PHP-FPM:

```bash
php -v
php -m
systemctl status php8.2-fpm
```

## 3. Composer Install

Install Composer if it is not already available:

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
rm composer-setup.php
```

## 4. Deploy The Application Code

Clone or upload the project:

```bash
sudo mkdir -p /var/www
sudo chown deploy:www-data /var/www
cd /var/www
git clone REPLACE_WITH_REPOSITORY_URL sales-crm-lite
cd /var/www/sales-crm-lite
```

Install PHP dependencies for production:

```bash
composer install --no-dev --optimize-autoloader
```

Install frontend dependencies and build the compiled CSS:

```bash
npm install
npm run build:css
```

## 5. Production Environment File

Create the production `.env` file:

```bash
nano .env
```

Use the values from `docs/production-env-example.md` as a template, but do not include the Markdown fences. Use a strong database password.

Minimum production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://crm.example.com
DB_DATABASE=sales_crm
DB_USERNAME=sales_crm_app
DB_PASSWORD=REPLACE_WITH_STRONG_RANDOM_PASSWORD
```

Lock down the environment file:

```bash
chmod 640 .env
sudo chown deploy:www-data .env
```

## 6. MySQL Or MariaDB Database Setup

Secure the database server:

```bash
sudo mysql_secure_installation
```

Create the database and application user:

```bash
sudo mysql
```

Run this SQL with your own password:

```sql
CREATE DATABASE sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sales_crm_app'@'localhost' IDENTIFIED BY 'REPLACE_WITH_STRONG_RANDOM_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES ON sales_crm.* TO 'sales_crm_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

For stricter operations, run migrations with a separate database owner account, then reduce the application user to `SELECT`, `INSERT`, `UPDATE`, and `DELETE`.

## 7. Run Migrations And Seeders

From the project directory:

```bash
cd /var/www/sales-crm-lite
```

Run migrations in order:

```bash
mysql -u sales_crm_app -p sales_crm < database/migrations/001_create_users_table.sql
mysql -u sales_crm_app -p sales_crm < database/migrations/002_create_failed_logins_table.sql
mysql -u sales_crm_app -p sales_crm < database/migrations/003_create_leads_table.sql
mysql -u sales_crm_app -p sales_crm < database/migrations/004_create_customers_table.sql
mysql -u sales_crm_app -p sales_crm < database/migrations/005_add_lead_conversion_fields.sql
mysql -u sales_crm_app -p sales_crm < database/migrations/006_create_follow_ups_table.sql
mysql -u sales_crm_app -p sales_crm < database/migrations/007_create_activities_table.sql
```

Seed the initial admin account only if you need first login access:

```bash
mysql -u sales_crm_app -p sales_crm < database/seeders/001_seed_admin_user.sql
```

Important:

- The local admin seeder uses demo credentials. Change the password immediately after first login.
- Do not run `database/seeders/002_seed_demo_crm_data.sql` on a real production database.
- Use the demo seeder only for disposable demo or portfolio environments.

## 8. File Permissions

Keep the code readable by Nginx/PHP-FPM and make only runtime log storage writable:

```bash
cd /var/www/sales-crm-lite
sudo chown -R deploy:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
chmod 640 .env
sudo chgrp -R www-data storage/logs
sudo chmod -R 775 storage/logs
```

Production logs are written to:

```text
storage/logs/app.log
```

## 9. Nginx Virtual Host

Copy the example config:

```bash
sudo cp docs/nginx-example.conf /etc/nginx/sites-available/sales-crm-lite
sudo nano /etc/nginx/sites-available/sales-crm-lite
```

Update:

- `server_name crm.example.com`
- `root /var/www/sales-crm-lite/public`
- PHP-FPM socket path, for example `/run/php/php8.2-fpm.sock`

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/sales-crm-lite /etc/nginx/sites-enabled/sales-crm-lite
sudo nginx -t
sudo systemctl reload nginx
```

## 10. HTTPS With Certbot

Install Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
```

The provided `docs/nginx-example.conf` is HTTP-first so Nginx can start before certificates exist. Let Certbot update the Nginx config and add HTTPS:

```bash
sudo certbot --nginx -d crm.example.com
sudo certbot renew --dry-run
```

After HTTPS is working:

- Confirm `.env` has `APP_URL=https://crm.example.com`.
- Confirm `.env` has `APP_ENV=production`.
- Confirm the app returns the `Strict-Transport-Security` header over HTTPS.

## 11. HTTPS With Caddy Alternative

If you prefer Caddy instead of Nginx, install Caddy and use a Caddyfile like:

```caddyfile
crm.example.com {
    root * /var/www/sales-crm-lite/public
    php_fastcgi unix//run/php/php8.2-fpm.sock
    file_server
    try_files {path} {path}/ /index.php?{query}
}
```

Caddy manages HTTPS certificates automatically. Do not run Nginx and Caddy on ports `80` and `443` at the same time.

## 12. Deployment Checklist

- DNS `A` record points to the VPS public IP.
- Oracle Cloud ingress rules allow ports `80` and `443`.
- UFW allows `OpenSSH` and `Nginx Full`.
- PHP-FPM, Nginx, MySQL/MariaDB, Composer, Node.js, and npm are installed.
- Required PHP extensions are installed.
- Code is deployed to `/var/www/sales-crm-lite`.
- `composer install --no-dev --optimize-autoloader` completed.
- `npm install` and `npm run build:css` completed.
- `.env` exists, is not committed, and uses production values.
- Database and user exist.
- Migrations ran in order.
- Demo seed data was not loaded into production.
- Web root points to `/var/www/sales-crm-lite/public`.
- `storage/logs` is writable by the PHP/web server user.
- HTTPS certificate is issued and auto-renewal works.
- `/health` returns an OK response.
- `APP_DEBUG=false` in production.

## 13. Troubleshooting

### 500 Internal Server Error

Check the application log and PHP-FPM log:

```bash
tail -n 100 storage/logs/app.log
sudo journalctl -u php8.2-fpm --no-pager -n 100
```

Common causes:

- `.env` missing or contains wrong database credentials.
- Migrations were not run.
- `storage/logs` is not writable.
- Composer dependencies were not installed.

### 404 On Valid App Routes

Confirm the Nginx `location /` block uses:

```nginx
try_files $uri $uri/ /index.php?$query_string;
```

Also confirm the Nginx root points to:

```text
/var/www/sales-crm-lite/public
```

### CSS Missing Or UI Looks Unstyled

Build Tailwind CSS:

```bash
npm run build:css
ls -la public/assets/css/app.css
```

### Login Works Locally But Not In Production

Confirm:

- The site is served over HTTPS.
- `APP_ENV=production`.
- `APP_URL=https://crm.example.com`.
- Browser cookies are not blocked.
- Server time is accurate: `timedatectl`.

### Certbot Fails

Check:

- DNS points to the server public IP.
- Oracle Cloud ingress allows port `80`.
- UFW allows `Nginx Full`.
- Nginx config passes: `sudo nginx -t`.

### Database Connection Fails

Check:

```bash
mysql -u sales_crm_app -p sales_crm
```

Confirm `.env` uses the same host, port, database, username, and password.
