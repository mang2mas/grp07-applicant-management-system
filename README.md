# Group 07 – Applicant Management System

A web-based Applicant Management System developed by Group 07 to streamline and manage the applicant recruitment process. The system provides a centralized platform for managing applicant information, departments, positions, interviews, and related recruitment records.

## Table of Contents

- [Project Overview](#project-overview)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Project Structure](#project-structure)
- [Installation and Setup](#installation-and-setup)
- [Database Configuration](#database-configuration)
- [Running the Application](#running-the-application)
- [Test Login Credentials](#test-login-credentials)
- [User Roles](#user-roles)
- [Troubleshooting](#troubleshooting)
- [AI Usage Declaration](#ai-usage-declaration)
- [Development Team](#development-team)

## Project Overview

The Group 07 Applicant Management System is designed to help organize and manage applicant-related information through a centralized web application.

The project uses PHP for server-side processing and PostgreSQL for database management. Access requires signing in; accounts have one of three roles (Administrator, Staff, Viewer) described in [User Roles](#user-roles).

## Technology Stack

| Component | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| CSS Framework | Tailwind CSS |
| Backend | PHP |
| Database | PostgreSQL |
| Database Connectivity | PDO / PostgreSQL |
| Development Environment | Visual Studio Code |
| Version Control | Git and GitHub |

## Prerequisites

Before installing the project, make sure the following are installed on your computer:

1. **PHP** – Required to execute the application.
2. **PostgreSQL** – Required for database management.
3. **pgAdmin 4** – Recommended for managing the PostgreSQL database.
4. **Visual Studio Code** – Recommended code editor.
5. **Git** – Required to clone the repository.

### Required PHP Extensions

Make sure the following PHP extensions are enabled:

- `pdo`
- `pdo_pgsql`

To check your PHP installation, open your terminal and execute:

```bash
php -v
```

To check the enabled extensions:

```bash
php -m
```

Make sure `PDO` and `pdo_pgsql` appear in the list.

## Project Structure

The project follows this general directory organization:

```text
grp07-applicant-management-system/
│
├── config/
│
├── db/
│   ├── 01_schema.sql
│   └── 02_seed.sql
│
├── docs/
│   ├── user-roles.md
│   └── troubleshooting.md
│
├── public/
│   └── index.php
│
├── src/
│   ├── actions/
│   └── views/
│   └── bootstrap.php
│   └── helpers.php
│
├── .env.example
├── .gitignore
└── README.md
```

**Note:** The directory structure above describes the main application components. Additional files and directories may be present in the repository.

## Installation and Setup

Follow these instructions to set up the project on your local machine.

### Step 1: Clone the Repository

Open your terminal and execute:

```bash
git clone https://github.com/mang2mas/grp07-applicant-management-system.git
```

Navigate to the project directory:

```bash
cd grp07-applicant-management-system
```

### Step 2: Open the Project

Open the project in Visual Studio Code:

```bash
code .
```

### Step 3: Configure Environment Variables

Create a local environment configuration file by copying the example:

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**Command Prompt:**

```cmd
copy .env.example .env
```

Open the `.env` file and configure your PostgreSQL connection credentials:

```env
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=grp07_applicantdb
DB_USER=your_postgres_username
DB_PASSWORD=your_postgres_password
DB_SSLMODE=disable
```

Replace the username and password with your actual PostgreSQL credentials.

**Important:** Never upload your `.env` file or expose your database credentials in a public repository.

## Database Configuration

The application uses PostgreSQL as its database management system.

### Step 1: Create the Database

Open pgAdmin 4 or your PostgreSQL SQL interface.

Create a new database named:

```sql
CREATE DATABASE grp07_applicantdb;
```

### Step 2: Execute the Database Schema

Open the SQL file:

```text
db/01_schema.sql
```

Execute the entire script against the `grp07_applicantdb` database.

This creates the database tables and their corresponding relationships, constraints, and other database structures, including the `users` table used for signing in.

### Step 3: Insert Initial Database Records

After successfully executing the schema, open:

```text
db/02_seed.sql
```

Execute the script against the same database.

This inserts the initial records required by the application, including the three test login accounts listed under [Test Login Credentials](#test-login-credentials).

**Important:** Always execute the schema before the seed file (`01_schema.sql` -> `02_seed.sql`). Some records depend on existing parent records and foreign key relationships.

Both scripts can be re-run safely. If your database was created before the `users` table was added, simply run both scripts again.

**Command-line alternative (psql):**

```bash
psql -U postgres -c "CREATE DATABASE grp07_applicantdb;"
psql -U postgres -d grp07_applicantdb -f db/01_schema.sql
psql -U postgres -d grp07_applicantdb -f db/02_seed.sql
```


## Running the Application

Once the database and environment configuration are ready, you can start the application.

### Step 1: Open the Project Terminal

Make sure your terminal is located in the root project directory:

```text
grp07-applicant-management-system/
```

### Step 2: Start the PHP Development Server

Execute:

```bash
php -S localhost:8000 -t public
```

The PHP development server should start successfully.

### Step 3: Open the Application

Open your preferred web browser and navigate to:

```text
http://localhost:8000
```

You will be redirected to the sign-in page. Sign in with one of the [test accounts](#test-login-credentials).

### Step 4: Stop the Development Server

To stop the running PHP development server, return to the terminal and press:

```text
CTRL + C
```

## Test Login Credentials

These accounts are created by `db/02_seed.sql` for classroom testing only.

| Username | Password | Role | Access |
|---|---|---|---|
| `admin` | `password123` | Administrator | Full create, read, update and delete |
| `staff` | `password123` | Staff | Full create, read, update and delete |
| `viewer` | `password123` | Viewer | Read-only (no create, edit or delete) |

Passwords are stored as BCRYPT hashes and checked with `password_verify()`. Change or delete these accounts before any real use. To reset a password, see [docs/troubleshooting.md](docs/troubleshooting.md).

## User Roles

| Application role | Intended PostgreSQL group role |
|---|---|
| Administrator | `app_admin_grp` |
| Staff | `app_runtime_grp` |
| Viewer | `app_readonly_grp` |

The PHP backend connects with a single runtime account (`app_runtime_grp`). Only the login page is public; every other page requires a session, and a Viewer is blocked from every write action on the server as well as having the Create, Edit and Delete controls hidden. See [docs/user-roles.md](docs/user-roles.md) for the full role matrix and the intended Activity 4 privileges.

## Troubleshooting

Here are some common issues you might encounter while setting up the application.

| Problem | Possible Solution |
|---|---|
| PHP command not recognized | Add PHP to your system PATH or verify your PHP installation. |
| Database connection failed | Check your PostgreSQL credentials and database configuration in `.env`. |
| PDO driver not found | Enable the `pdo_pgsql` PHP extension. |
| Database does not exist | Make sure `grp07_applicantdb` has been created. |
| Foreign key constraint violation | Execute the schema first and ensure referenced parent records exist before inserting dependent records. |
| Address already in use | Use another available port, such as `php -S localhost:8001 -t public`. |
| Page not found | Make sure the PHP server is running with `public` as its document root. |
| Cannot sign in | Run `db/02_seed.sql` so the `users` table has the test accounts, then use `admin` / `password123`. |
| `relation "users" does not exist` | Run `db/01_schema.sql` again, then `db/02_seed.sql`. |

More detail, including XAMPP `pdo_pgsql` setup, `.env` problems and PostgreSQL service fixes, is in [docs/troubleshooting.md](docs/troubleshooting.md).

## AI Usage Declaration

**AI tool used:** Claude (Anthropic), used as a coding assistant (Claude Sonnet 5.5).

**Tasks performed with AI assistance**

- Drafted the first versions of the PostgreSQL schema (`db/01_schema.sql`) and seed data (`db/02_seed.sql`) from the Group 7 Activity 2 blueprint.
- Drafted the initial PDO connection module and the first PHP views and action handlers for applicants, applications and positions.
- Added the authentication system on top of the group's finished CRUD modules: the `users` table and seeded accounts, the login view and handler, logout, session guards in `public/index.php`, the header user/logout display, and hiding of write controls for the Viewer role.
- Drafted `docs/user-roles.md`, `docs/troubleshooting.md` and the updates to this README.
- Ran automated checks in the assistant's own environment: PHP syntax checks on every file, loading the SQL files into a local PostgreSQL 16 test instance, and scripted requests covering sign-in, all three roles, CSRF rejection, and a check that the existing CRUD pages still load.

**Not done by AI:** the Activity 2 database design, the departments, interviews, requirements and applicant-requirements modules and the other work the group built itself, and the final run on the group's PostgreSQL 18.6 server.

**Validation performed by Group 7** (each member must confirm before submission; edit this list to match what was actually done)

- [ ] Executed `01_schema.sql` then `02_seed.sql` on PostgreSQL 18.6 with zero errors and confirmed the `users` table has 3 rows.
- [ ] Signed in as `admin`, `staff` and `viewer` and confirmed the Viewer cannot create, edit or delete on any screen.
- [ ] Confirmed unauthenticated visits to any page redirect to the sign-in page, and that Logout ends the session.
- [ ] Confirmed every existing CRUD screen (applicants, applications, departments, positions, interviews, requirements, applicant requirements) still works when signed in as `admin` or `staff`.
- [ ] Reviewed the SQL and PHP code and can explain the password hashing, session handling, CSRF tokens and prepared statements.
- [ ] Confirmed `.env` is not committed and no real passwords are in the repository.

Group 7 remains responsible for the submitted work; AI output was reviewed and tested before use.

## Development Team

**Group 07**

Developed as an academic project for educational purposes.

**Repository:**  
https://github.com/mang2mas/grp07-applicant-management-system

---

*Group 07 – Applicant Management System*
