# Sales CRM Lite — Local Development Setup

## Prerequisites

| Tool | Version | Check |
|---|---|---|
| PHP | 8.2+ | `php -v` |
| MySQL / MariaDB | 8.0+ / 10.6+ | `mysql --version` |
| Composer | 2.x | `composer --version` |
| Git | any | `git --version` |

---

## 1. Clone the Repository

```bash
git clone https://github.com/your-username/sales-crm-lite.git
cd sales-crm-lite
```

---

## 2. Install PHP Dependencies

```bash
composer install
```

This sets up PSR-4 autoloading. No framework packages are installed.

---

## 3. Configure the Environment

```bash
cp .env.example .env
```

Open `.env` in your editor and fill in the values:

```env
APP_NAME="Sales CRM Lite"
APP_ENV=development
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sales_crm
DB_USER=your_db_user
DB_PASS=your_db_password

SESSION_LIFETIME=7200
SESSION_NAME=crm_session
```

> The `.env` file is listed in `.gitignore` and is never committed to the repository.

---

## 4. Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## 5. Run Migrations

Apply the schema in order. Migration files are numbered and must be run sequentially.

```bash
mysql -u root -p sales_crm < database/migrations/001_create_users_table.sql
```

Verify the table was created:

```bash
mysql -u root -p sales_crm -e "DESCRIBE users;"
```

Expected output:

```
+---------------+---------------------------+------+-----+-------------------+...
| Field         | Type                      | Null | Key | Default           |
+---------------+---------------------------+------+-----+-------------------+
| id            | int unsigned              | NO   | PRI | NULL              |
| first_name    | varchar(100)              | NO   |     | NULL              |
| last_name     | varchar(100)              | NO   |     | NULL              |
| email         | varchar(255)              | NO   | UNI | NULL              |
| password_hash | varchar(255)              | NO   |     | NULL              |
| role          | enum('admin','sales_rep') | NO   |     | sales_rep         |
| is_active     | tinyint(1)                | NO   |     | 1                 |
| last_login_at | datetime                  | YES  |     | NULL              |
| created_at    | timestamp                 | NO   |     | CURRENT_TIMESTAMP |
| updated_at    | timestamp                 | NO   |     | CURRENT_TIMESTAMP |
+---------------+---------------------------+------+-----+-------------------+
```

---

## 6. Create the Admin User

The seed file does not contain a plain-text password. You generate the hash locally.

### Step 6a — Generate a bcrypt hash

Run this in your terminal and replace `your_password` with a secure password of your choice:

```bash
php -r "echo password_hash('your_password', PASSWORD_BCRYPT, ['cost' => 12]) . PHP_EOL;"
```

The output will look like this (your hash will differ):

```
$2y$12$K8qX7vZ2pL1mN3oR5wT9ueRbJcH6gA4dY0fI8sW2lP7nU3hE9kQ1.
```

### Step 6b — Edit the seeder

Open `database/seeders/001_seed_admin_user.sql` and replace `REPLACE_WITH_BCRYPT_HASH` with your generated hash:

```sql
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password_hash`, `role`, `is_active`)
VALUES (
    'Admin',
    'User',
    'admin@example.com',
    '$2y$12$K8qX7vZ2pL1mN3oR5wT9ueRbJcH6gA4dY0fI8sW2lP7nU3hE9kQ1.',  -- your hash
    'admin',
    1
);
```

### Step 6c — Run the seeder

```bash
mysql -u root -p sales_crm < database/seeders/001_seed_admin_user.sql
```

Verify the user was inserted:

```bash
mysql -u root -p sales_crm -e "SELECT id, first_name, email, role, is_active FROM users;"
```

---

## 7. Start the Development Server

```bash
php -S localhost:8000 -t public/
```

Open [http://localhost:8000/login](http://localhost:8000/login) in your browser.

Sign in with:
- **Email:** `admin@example.com`
- **Password:** the password you chose in Step 6a

---

## 8. Verify the Setup

### Check the login page loads

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/login
# Expected: 200
```

### Check that unauthenticated access to protected routes redirects

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/
# Expected: 302 (redirect to /login)
```

### Verify the password hash is valid

```bash
php -r "
\$hash = 'PASTE_YOUR_HASH_HERE';
var_dump(password_verify('your_password', \$hash));
// Expected: bool(true)
"
```

---

## Resetting the Database

To start fresh during development:

```bash
mysql -u root -p -e "DROP DATABASE sales_crm; CREATE DATABASE sales_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p sales_crm < database/migrations/001_create_users_table.sql
mysql -u root -p sales_crm < database/seeders/001_seed_admin_user.sql
```

---

## Troubleshooting

### "Dependencies not installed"

```bash
composer install
```

### "Access denied for user"

Check `DB_USER` and `DB_PASS` in your `.env` file. Confirm the MySQL user has privileges on the `sales_crm` database:

```sql
GRANT ALL PRIVILEGES ON sales_crm.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

### Session not persisting

- Confirm PHP has write access to the session directory: `php -r "echo session_save_path();"`
- On some systems: `chmod 777 /tmp` or configure a writable path in `php.ini`

### "REPLACE_WITH_BCRYPT_HASH" error on login

You ran the seeder without replacing the placeholder. Repeat Step 6b and re-run Step 6c.

---

## Notes on the Column Design

The `users` table uses `first_name` and `last_name` as separate columns rather than a single `name` field. This allows:

- Proper salutations ("Dear Alice")
- Sorting by last name
- Consistent display across views ("Alice M.", "A. Miller")
