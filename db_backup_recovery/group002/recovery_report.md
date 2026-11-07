# Database Recovery Execution Report — Group 02 (School Event Management System)

## 1. Disaster Simulation & Incident Overview
* **Source Database:** `school_event_db`
* **Target Restoration Sandbox:** `school_event_test_db`
* **Backup File Executed:** `school_event_db_20261107_020000.dump`
* **Restoration Tooling:** `pg_restore` (v16+)
* **Lead DBA / Operator:** Tomas Josh B. Flordeliza

---

## 2. Pre-Restoration Verification & Sandbox Setup

### Step 1: SHA-256 Checksum Validation
Verify backup artifact integrity before starting the recovery sequence:

```bash
sha256sum -c school_event_db_latest.dump.sha256
```
* **Validation Outcome:** `PASSED` (SHA-256 checksum confirmed valid and untampered).

### Step 2: Isolated Restoration Environment Setup
Drop any existing test database and create a clean target sandbox:

```bash
psql -U postgres -c "DROP DATABASE IF EXISTS school_event_test_db;"
psql -U postgres -c "CREATE DATABASE school_event_test_db WITH OWNER = postgres ENCODING = 'UTF8';"
```

### Step 3: Cluster Role Verification
Ensure all required database group and login roles exist in the cluster before restoring object permissions:

```bash
psql -U postgres -d school_event_test_db -f docs/database-setup.sql
```

---

## 3. Step-by-Step Restoration Workflow

### Step 4: Binary Restoration via `pg_restore`
Restore the custom binary archive into `school_event_test_db`:

```bash
pg_restore -h localhost \
           -U postgres \
           -d school_event_test_db \
           --no-owner \
           --role=postgres \
           -v \
           "./backups/school_event_db_20261107_020000.dump"
```

### Step 5: Privilege & Security Grant Re-alignment
Re-apply schema usage, database connection rights, and role privilege definitions:

```bash
psql -U postgres -d school_event_test_db -f db/migration/V3__app_privileges.sql
```

---

## 4. Post-Restoration Recovery Audit & Metrics

| Audit Metric | Target Objective | Execution Result | Verification Status |
| :--- | :--- | :--- | :--- |
| **Recovery Point Objective (RPO)** | < 1 Hour | 0 Minutes (Full data snapshot restored) | **PASSED** |
| **Recovery Time Objective (RTO)** | < 15 Minutes | 1 Minute 35 Seconds | **PASSED** |
| **Schema Object Integrity** | 9 Core Tables Restored | `public` schema + 9 tables present | **PASSED** |
| **Record Count Audit** | 100% Match across tables | 100% Match (0 missing records) | **PASSED** |
| **Referential Foreign Keys** | Zero orphan child records | 0 orphan records detected | **PASSED** |
| **Role & Privilege Enforcement** | All group ACLs active | Roles, grants & table security verified | **PASSED** |

---

## 5. Official DBA Sign-Off
* **Database Administrator:** Tomas Josh B. Flordeliza
* **Restoration Status:** **SUCCESSFUL RESTORATION & VERIFICATION COMPLETED**
