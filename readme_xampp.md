# Group 7 - Applicant Management System (AMS)

An Applicant Management System built for **COMP 018 Database Administration (Activity 3)** using PHP 8.x, PostgreSQL 18.6, and Tailwind CSS. The system provides role-based access control, session management, and CRUD management across 8 database tables.

---

## 📌 Prerequisites

Before running the project locally, ensure you have the following installed on your machine:

- **XAMPP** (with PHP 8.x installed at `C:\xampp\php\php.exe`)
- **PostgreSQL 18** (installed at `C:\Program Files\PostgreSQL\18\bin\psql.exe`)
- **Git**

---

## ⚙️ XAMPP & PHP Configuration

PostgreSQL extensions must be enabled in XAMPP's `php.ini` file before launching the server:

1. Open `C:\xampp\php\php.ini` in a text editor (or click **Config** > **php.ini** next to Apache in the XAMPP Control Panel).
2. Search for `pdo_pgsql` and `pgsql`.
3. Remove the leading semicolon `;` from both lines so they look like this:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
4. Save the file.

---

## 🚀 Environment Setup & Installation

### 1. Clone the Repository
Open PowerShell and navigate to your working directory:
```powershell
git clone <your-repository-url>
cd grp07-applicant-management-system
```

### 2. Configure Environment File (`.env`)
Create a `.env` file in the project root by copying `.env.example`:
```powershell
Copy-Item .env.example .env
```
Open `.env` in VS Code and update your database credentials matching your local PostgreSQL installation:
```env
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=grp07_applicantdb
DB_USER=postgres
DB_PASSWORD=your_actual_postgres_password
DB_SSLMODE=disable
```

---

## 🗄️ Database Initialization (PostgreSQL)

Run the following commands in PowerShell to create the database, reset schema objects, and seed initial test data.

### Step 1: Create Database
Open `psql` or run directly in PowerShell:
```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -c "CREATE DATABASE grp07_applicantdb;"
```

### Step 2: Import Schema & Seed Data
Execute the SQL files in exact numerical order:

```powershell
# 1. Clear existing schema objects (Fresh Reset)
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d grp07_applicantdb -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"

# 2. Run Database Schema Script
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d grp07_applicantdb -f db/01_schema.sql

# 3. Seed Test Accounts & Initial Data
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d grp07_applicantdb -f db/02_seed.sql
```

---

## 💻 Running the Application

Launch the local PHP server using the XAMPP PHP executable:

```powershell
& "C:\xampp\php\php.exe" -S localhost:8000 -t public
```

Open your browser and visit:  
👉 **`http://localhost:8000`**

---

## 🔑 Test Accounts & Role Access

Log in using one of the pre-seeded accounts created in `db/02_seed.sql`:

| Role | Username | Password | Access Rights & Privileges |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `password123` | Full system control; CRUD operations across all modules. |
| **Staff** | `staff` | `password123` | Operational access; create and update applicants, applications, & interviews. |
| **Viewer** | `viewer` | `password123` | Read-only access; action buttons hidden, server blocks unauthorized POST requests (403 Forbidden). |

For detailed mapping between Application Roles and PostgreSQL Group Roles, refer to [`docs/user-roles.md`](docs/user-roles.md).

---

## 📁 Project Structure

```text
grp07-applicant-management-system/
├── config/             # Database connection setup (PDO)
├── db/                 # PostgreSQL DDL schema & seed files
│   ├── 01_schema.sql
│   └── 02_seed.sql
├── docs/               # System documentation
│   ├── troubleshooting.md
│   └── user-roles.md
├── public/             # Web server entry point & router
│   ├── index.php
│   └── assets/
├── src/                # Application logic & views
│   ├── actions/        # POST process handlers (CRUD & Auth)
│   ├── views/          # Module UI templates (Tailwind CSS)
│   ├── bootstrap.php
│   └── helpers.php
├── .env.example        # Environment variable template
├── .gitignore          # Git exclusion rules
└── README.md           # System documentation
```

---

## 🔧 Troubleshooting

If you encounter a `500 Server Error` or PDO database connection issue, refer to [`docs/troubleshooting.md`](docs/troubleshooting.md) for step-by-step resolution on:
- Enabling missing PHP extensions in XAMPP.
- Fixing PostgreSQL password/connection mismatches in `.env`.
- Resetting database tables when schema changes occur.

---

## 🤖 AI Usage Declaration

This project leveraged AI tools (such as Claude and ChatGPT) as virtual writing and debugging assistants during Activity 3 development.

### Validation Checklist
- [x] All database schema scripts executed without errors on PostgreSQL 18.
- [x] All 8 CRUD table modules verified functional via browser UI.
- [x] Role-Based Access Control verified (Viewer restricted from unauthorized actions via 403 response).
- [x] Codebase audited to ensure no sensitive credentials or plain-text passwords exist in source files.