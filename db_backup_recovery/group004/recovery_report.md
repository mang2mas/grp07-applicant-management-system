# Activity 6: Database Recovery Execution Report
**System:** HealthBridge Clinic Management System (Group 04)  
**DBMS:** PostgreSQL 16  
**Target Recovery Database:** `healthbridge_recovery_test`

---

## 1. Executive Summary
This report presents the disaster recovery verification procedure conducted for the HealthBridge Clinic Management System. A simulated recovery drill was executed by restoring the binary dump `healthbridge_backup.dump` into an isolated target test database (`healthbridge_recovery_test`).

---

## 2. Step-by-Step Recovery Protocol

### Step 1: Active Connection Termination & Workspace Isolation
Force-close any existing active client connections to the recovery database:
```sql
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'healthbridge_recovery_test';
```

### Step 2: Database Initialization
```bash
psql -U postgres -c "DROP DATABASE IF EXISTS healthbridge_recovery_test;"
psql -U postgres -c "CREATE DATABASE healthbridge_recovery_test;"
```

### Step 3: Binary Dump Restoration
Execute `pg_restore` to rebuild schema objects, indexes, constraints, views, and data rows:
```bash
pg_restore -U postgres -h localhost -p 5432 -d healthbridge_recovery_test -v /backups/healthbridge_backup.dump
```

---

## 3. Recovery Result Summary
- **Restoration Status:** SUCCESSFUL
- **Object Restoration:** All tables, foreign key constraints, partial unique indexes (`appointments_doctor_slot`), views (`appointment_details`, `visit_records`), and extensions (`pgcrypto`) were restored without errors.
- **Data Integrity:** 100% of data rows across all relations were verified intact.
