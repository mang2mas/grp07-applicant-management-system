# User Roles and Database Role Mapping

**Project:** Applicant Management System (AMS) - Group 7
**Database:** `grp07_applicantdb` (PostgreSQL, schema `public`)

This document separates two ideas:

- **Application roles** - what a person can do after signing in to the AMS web application. The role is stored in `users.role` and kept in the session as `$_SESSION['role']`.
- **Database roles** - what a PostgreSQL account is allowed to do. These are the group roles to be created in Activity 4.

An application role is **not** a PostgreSQL login. People never connect to PostgreSQL directly; the PHP backend connects through one dedicated account (`DB_USER` in `.env`).

> No real passwords are stored in this repository. The three test web logins use the throw-away password `password123`; change or delete them outside a classroom demo. Every database password below is a placeholder.

---

## 1. Role Mapping

| Application role | Who it is for | Web application access | Intended PostgreSQL group role |
|---|---|---|---|
| **Administrator** | System owner / database administrator | Full create, read, update and delete on every screen | `app_admin_grp` |
| **Staff** | HR staff / recruiters doing day-to-day work | Full create, read, update and delete on every screen | `app_runtime_grp` |
| **Viewer** | Auditors and reviewers | Read-only: can browse every page, but cannot create, edit or delete anything | `app_readonly_grp` |
| **Application runtime** (system account, not a person) | The login the PHP backend uses (`DB_USER`) | Whatever the signed-in user's role allows | `app_runtime_grp` |

Notes:

- In the **current build, Administrator and Staff have identical web permissions**; only Viewer is restricted. The two roles are kept separate in the `users` table so Administrator-only features (for example user management) can be added later without changing the data model.
- `app_admin_grp` is an elevated group role for administration, **not** a PostgreSQL superuser.
- Staff and the application runtime account share `app_runtime_grp`, so the backend can never do more than Staff are meant to do.

## 2. What Each Role Can Do in the Web Application

`R` = view, `CUD` = create, update and delete, `-` = not available.

| Screen | Administrator | Staff | Viewer |
|---|---|---|---|
| Dashboard | R | R | R |
| Applicants | R + CUD | R + CUD | R |
| Applicant Requirements | R + CUD | R + CUD | R |
| Applications (incl. status change) | R + CUD | R + CUD | R |
| Positions | R + CUD | R + CUD | R |
| Departments | R + CUD | R + CUD | R |
| Requirements | R + CUD | R + CUD | R |
| Interviews | R + CUD | R + CUD | R |
| System Status | R | R | R |

## 3. How the Application Enforces Roles

- **Login:** `login_process.php` looks the username up in `users` and checks the password with `password_verify()` against the BCRYPT hash. On success it stores `user_id`, `username` and `role` in the session and regenerates the session ID.
- **Session guard:** `public/index.php` allows only the `login` page and the `login_process` action without a session. Every other page and action redirects to the login page.
- **Viewer restriction (server side):** a Viewer receives a 403 for every POST action (except logout) and for every create/edit form page, so a hand-crafted request cannot change data.
- **Viewer restriction (interface):** the Create, Edit, Delete, Assign and status-update controls are hidden when `$_SESSION['role'] === 'Viewer'`.
- All forms carry a CSRF token and all SQL uses PDO prepared statements.

## 4. Intended PostgreSQL Privileges per Table

`C` = INSERT, `R` = SELECT, `U` = UPDATE, `D` = DELETE, `-` = no access.

| Table | `app_admin_grp` | `app_runtime_grp` (Staff + runtime) | `app_readonly_grp` (Viewer) |
|---|---|---|---|
| `department` | CRUD | CRUD | R |
| `position` | CRUD | CRUD | R |
| `applicant` | CRUD | CRUD | R |
| `application_status` | CRUD | R | R |
| `application` | CRUD | CRUD | R |
| `interview` | CRUD | CRUD | R |
| `requirement` | CRUD | CRUD | R |
| `applicant_requirement` | CRUD | CRUD | R |
| `users` | CRUD | R, and U on `password_hash` only | - |

The runtime group needs `SELECT` on `users` to sign people in, and `UPDATE (password_hash)` because the login handler upgrades an old hash if the algorithm or cost changes.

Because the web application uses a single database connection, the Viewer restriction is enforced by the application (section 3), not by PostgreSQL. `app_readonly_grp` is for a read-only reporting or audit login (`ams_viewer`) that connects to the database directly.

## 5. Intended Login Accounts

| Database login (intended) | Member of | Used by |
|---|---|---|
| `ams_admin` | `app_admin_grp` | Database administration |
| `ams_app_user` | `app_runtime_grp` | The PHP backend (`DB_USER` in `.env`) |
| `ams_viewer` | `app_readonly_grp` | Read-only reporting / audit access |

Test web logins (table `users`, created by `db/02_seed.sql`):

| Username | Role | Password |
|---|---|---|
| `admin` | Administrator | `password123` |
| `staff` | Staff | `password123` |
| `viewer` | Viewer | `password123` |

## 6. Reference: Intended Role Setup (for Activity 4)

This SQL documents the intended design. It is **not** run by any setup step. Replace each `<set-a-strong-password>` locally and never commit the result.

```sql
-- Group roles (no login)
CREATE ROLE app_admin_grp    NOLOGIN;
CREATE ROLE app_runtime_grp  NOLOGIN;
CREATE ROLE app_readonly_grp NOLOGIN;

-- Connection and schema access
GRANT CONNECT ON DATABASE grp07_applicantdb TO app_admin_grp, app_runtime_grp, app_readonly_grp;
GRANT USAGE ON SCHEMA public TO app_admin_grp, app_runtime_grp, app_readonly_grp;

-- Administrator group: full CRUD, not a superuser
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_admin_grp;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_admin_grp;

-- Runtime group (Staff + the PHP backend)
GRANT SELECT, INSERT, UPDATE, DELETE
    ON department, position, applicant, application, interview, requirement, applicant_requirement
    TO app_runtime_grp;
GRANT SELECT ON application_status TO app_runtime_grp;
GRANT SELECT ON users TO app_runtime_grp;
GRANT UPDATE (password_hash) ON users TO app_runtime_grp;
GRANT USAGE, SELECT ON SEQUENCE
    department_department_id_seq,
    position_position_id_seq,
    applicant_applicant_id_seq,
    application_application_id_seq,
    interview_interview_id_seq,
    requirement_requirement_id_seq
    TO app_runtime_grp;

-- Read-only group (Viewer / auditor)
GRANT SELECT ON department, position, applicant, application_status, application,
                interview, requirement, applicant_requirement
    TO app_readonly_grp;

-- Login accounts (placeholders only)
CREATE ROLE ams_admin    LOGIN PASSWORD '<set-a-strong-password>' IN ROLE app_admin_grp;
CREATE ROLE ams_app_user LOGIN PASSWORD '<set-a-strong-password>' IN ROLE app_runtime_grp;
CREATE ROLE ams_viewer   LOGIN PASSWORD '<set-a-strong-password>' IN ROLE app_readonly_grp;
```

Sequence names follow PostgreSQL's `<table>_<column>_seq` pattern; confirm them with `\ds` before running the grants.
