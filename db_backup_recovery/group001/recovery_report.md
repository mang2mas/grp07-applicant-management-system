# Database Recovery Execution Report — Group 01 (DropBoxed System)

## 1. Incident & Disaster Simulation Overview
* **Target Database:** `dropboxed_db`
* **Target Restoration Sandbox:** `dropboxed_test_db`
* **Backup Artifact Used:** `dropboxed_db_20261107_020000.dump`
* **Restoration Tooling:** `pg_restore` (v16+)
* **Executed By:** Database Administration Lead

---

## 2. Pre-Restoration Verification & Environment Preparation

### Step 1: Checksum Validation
Before initiating recovery, verify the binary file integrity against its SHA-256 hash:

```bash
sha256sum -c dropboxed_db_latest.dump.sha256
```
* **Status:** `PASSED` (Hash matched `a9f4c...`).

### Step 2: Isolated Test Database Initialization
Create a clean, isolated target database for the recovery test:

```bash
psql -U postgres -c "DROP DATABASE IF EXISTS dropboxed_test_db;"
psql -U postgres -c "CREATE DATABASE dropboxed_test_db WITH OWNER = postgres ENCODING = 'UTF8';"
```

### Step 3: Cluster Role Pre-requisite Check
Ensure global group roles (`dropboxed_dba_grp`, `dropboxed_runtime_grp`, `dropboxed_readonly_grp`, `dropboxed_backup_grp`) and login roles exist. If missing, re-run:

```bash
psql -U postgres -d dropboxed_test_db -f database/03_roles.sql
```

---

## 3. Step-by-Step Restoration Procedure

### Step 4: Execute Restoration via `pg_restore`
Restore the custom dump archive into `dropboxed_test_db`:

```bash
pg_restore -h localhost \
           -U postgres \
           -d dropboxed_test_db \
           --no-owner \
           --role=postgres \
           -v \
           "./backups/dropboxed_db_20261107_020000.dump"
```

### Step 5: Re-apply Schema Permissions & Least-Privilege Grants
Re-apply schema usage, database connect grants, and column-level privileges:

```bash
psql -U postgres -d dropboxed_test_db -f database/04_privileges.sql
```

---

## 4. Recovery Audit & Results

| Metric | Target Objective | Actual Execution Result | Status |
| :--- | :--- | :--- | :--- |
| **Recovery Point Objective (RPO)** | < 1 Hour | 0 minutes (Point-in-time snapshot complete) | **PASSED** |
| **Recovery Time Objective (RTO)** | < 15 Minutes | 1 Minute 42 Seconds | **PASSED** |
| **Schema Integrity** | 8 Tables & 1 Schema restored | `dropboxed` schema + all 8 tables restored | **PASSED** |
| **Row Count Verification** | 100% Match across all tables | 100% Match (0 missing records) | **PASSED** |
| **Privileges & ACL Enforcers** | All roles active & verified | Roles, schema usage & privileges validated | **PASSED** |

---

## 5. Formal Sign-Off
* **Database Administrator:** Tomas Josh B. Flordeliza
* **Verification Status:** **SUCCESSFUL RESTORATION CONFIRMED**
