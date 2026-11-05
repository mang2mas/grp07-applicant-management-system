# Troubleshooting Guide

Quick fixes for common setup problems in the Applicant Management System (AMS).

## 1. XAMPP: enabling `pdo_pgsql`

XAMPP includes the PostgreSQL drivers for PHP but leaves them **disabled**. AMS needs both `pdo_pgsql` and `pgsql`.

1. Check what is loaded: `C:\xampp\php\php.exe -m` (look for `pdo_pgsql` and `pgsql`).
2. Open `C:\xampp\php\php.ini` (XAMPP Control Panel -> Apache -> Config -> PHP (php.ini)).
3. Remove the leading `;` from these lines:

   ```
   extension=pdo_pgsql
   extension=pgsql
   ```
4. Save, then **restart Apache** (or stop and restart `php -S`).
5. Run `php -m` again and confirm both modules appear.

| Symptom | Cause | Fix |
|---|---|---|
| `could not find driver` | `pdo_pgsql` is not enabled, or you edited a different `php.ini`. | Run `php --ini` to see which file is loaded and edit that one. |
| Module still missing, warning about `php_pdo_pgsql.dll` | `libpq.dll` cannot be found. | Add `C:\xampp\php` to `PATH`, or copy `libpq.dll` from `C:\Program Files\PostgreSQL\<version>\bin` next to `php.exe`. |
| `php` is not recognized | PHP is not on `PATH`. | Use the full path (`C:\xampp\php\php.exe`) or add `C:\xampp\php` to `PATH`. |
| Running under Apache | The web root must be `public/`, or `.env` and `src/` would be exposed. | Set `DocumentRoot` to `.../grp07-applicant-management-system/public`, or use `php -S localhost:8000 -t public`. |

## 2. `.env` configuration

Copy `.env.example` to `.env` in the project root (next to `README.md`) and fill in your own values:

```
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=grp07_applicantdb
DB_USER=your_postgres_username
DB_PASSWORD=your_postgres_password
DB_SSLMODE=disable
```

| Symptom | Cause | Fix |
|---|---|---|
| "Database configuration is incomplete" | `.env` is missing, misnamed, or `DB_USER` / `DB_PASSWORD` are empty. | Create `.env` from `.env.example`. On Windows make sure the file is not `.env.txt`. |
| Edits to `.env` have no effect | The server was not restarted, or a real environment variable overrides the file. | Restart `php -S` / Apache; remove stale `DB_*` variables from the OS environment. |
| Password containing `#`, spaces or quotes fails | Special characters in the value. | Wrap the value in double quotes: `DB_PASSWORD="my pass#word"`. |
| `.env` shows up in `git status` | It is not ignored. | `.env` must be listed in `.gitignore`. Never commit it. |

## 3. PostgreSQL service and connection

| Symptom | Cause | Fix |
|---|---|---|
| "Unable to connect to the database" | PostgreSQL is not running, or host, port, name or credentials are wrong. | See the rows below. The real error is written to the `php -S` console / PHP error log (lines start with `[AMS]`). |
| `connection refused` | The PostgreSQL service is stopped. | **Windows:** `Win+R` -> `services.msc` -> start `postgresql-x64-<version>` (set it to Automatic). **Linux:** `sudo systemctl start postgresql`. |
| `password authentication failed` | Wrong `DB_PASSWORD`. | Fix `.env`. To reset: `ALTER ROLE <user> PASSWORD '<new>';` as a superuser. |
| `database "grp07_applicantdb" does not exist` | The database was never created. | `psql -U postgres -c "CREATE DATABASE grp07_applicantdb;"` then load the SQL files (section 4). |
| Works in `psql`, fails in PHP | Different host or port. | Use `DB_HOST=127.0.0.1`; if PostgreSQL listens on another port (for example 5433), set `DB_PORT` to match. |
| `no pg_hba.conf entry for host ...` | The server does not allow that user or host. | Add `host all all 127.0.0.1/32 scram-sha-256` to `pg_hba.conf` and reload the service. |
| `psql` is not recognized (Windows) | PostgreSQL's `bin` folder is not on `PATH`. | Add `C:\Program Files\PostgreSQL\<version>\bin` to `PATH` and reopen the terminal. |
| SSL error | `DB_SSLMODE` does not match the server. | For a local server without SSL use `DB_SSLMODE=disable`. |

Quick test: `psql -h 127.0.0.1 -U <user> -d grp07_applicantdb -c "SELECT 1;"`

## 4. SQL scripts and sign-in

Run the scripts in this order against `grp07_applicantdb`:

```
psql -U postgres -d grp07_applicantdb -f db/01_schema.sql
psql -U postgres -d grp07_applicantdb -f db/02_seed.sql
```

(In pgAdmin 4: open each file in the Query Tool on `grp07_applicantdb`, schema first, then seed.)

| Symptom | Cause | Fix |
|---|---|---|
| `relation "users" does not exist` | You have a database created before the `users` table was added. | Run `db/01_schema.sql` again (it is safe to repeat), then `db/02_seed.sql`. |
| "Invalid username or password" for `admin`, `staff` or `viewer` | The seed was not loaded, or the password was mistyped (it is lowercase `password123`). | Run `db/02_seed.sql`; check with `SELECT username, role FROM users;`. |
| Need to reset a password | Forgotten or changed. | `php -r "echo password_hash('newpassword', PASSWORD_BCRYPT);"` then `UPDATE users SET password_hash = '<hash>' WHERE username = 'admin';` |
| Sent back to the login page repeatedly | Cookies are blocked, or PHP cannot write sessions. | Allow cookies for `localhost`; check that `session.save_path` exists and is writable. |
| 403 "Your Viewer account is read-only" | The `viewer` account tried to create, edit or delete. | Expected. Sign in as `admin` or `staff` for write access. |
| 419 "Your session expired or the form was invalid" | The CSRF token did not match (stale tab, or session ended). | Reload the page and submit again. |
| `relation "applicant" does not exist` | `01_schema.sql` was not run, or ran in the wrong database. | Run it with `-d grp07_applicantdb`; confirm with `\dt`. |
| Foreign key violation while seeding | Scripts run out of order. | Run `01_schema.sql` before `02_seed.sql`. |
| "In use" instead of a Delete button | The record is referenced by other records (foreign key). | Remove or reassign the dependent records first. |

## 5. Other

| Symptom | Fix |
|---|---|
| `Failed to listen on localhost:8000` | Port in use: `php -S localhost:8001 -t public`. |
| Page looks unstyled | Tailwind CSS loads from a CDN and needs internet access. |
| Blank page or HTTP 500 | PHP errors are hidden from the browser by design; read the `php -S` console or Apache `error.log`. |
