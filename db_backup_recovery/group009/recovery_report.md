# Activity 6: Database Recovery Execution Report
**System:** Gym Management System (Group 09)  
**DBMS:** PostgreSQL 16  
**Target Recovery Database:** `gym_recovery_test`

---

## 1. Executive Summary
This report details the disaster recovery verification procedure performed for the Gym Management System. A simulated system restoration test was conducted by restoring the production backup `gym_backup.dump` into an isolated target environment (`gym_recovery_test`).

---

## 2. Step-by-Step Recovery Protocol

### Step 1: Active Connection Termination & Isolation
Force-close any active client connections to the recovery database prior to re-creation:
```sql
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'gym_recovery_test';
```

### Step 2: Database Initialization
```bash
psql -U postgres -c "DROP DATABASE IF EXISTS gym_recovery_test;"
psql -U postgres -c "CREATE DATABASE gym_recovery_test;"
```

### Step 3: Binary Dump Restoration
Execute `pg_restore` to restore the schema, partial unique indexes, foreign key constraints, and seed data records:
```bash
pg_restore -U postgres -h localhost -p 5432 -d gym_recovery_test -v /backups/gym_backup.dump
```

---

## 3. Recovery Result Summary
- **Restoration Status:** SUCCESSFUL
- **Integrity Validation:** All foreign key constraints, check constraints (`duration_months > 0`, `price >= 0`, `end_date > start_date`), and partial unique indexes (`unique_active_membership`) were successfully restored and validated.
- **Data Completeness:** 100% record match verified across all 8 base tables.
