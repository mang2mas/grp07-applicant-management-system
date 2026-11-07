# Activity 6: Database Recovery Execution Report
**System:** Car Rental Management System (Group 06)  
**DBMS:** PostgreSQL 16  
**Target Recovery Database:** `car_rental_recovery_test`

---

## 1. Executive Summary
This report documents the disaster recovery verification procedure conducted for the Car Rental Management System. A simulated system restoration test was performed by executing a full recovery of `car_rental_backup.dump` into an isolated verification environment (`car_rental_recovery_test`).

---

## 2. Step-by-Step Recovery Protocol

### Step 1: Active Connection Termination & Environment Isolation
Forcibly terminate active sessions to the recovery database prior to re-creation:
```sql
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'car_rental_recovery_test';
```

### Step 2: Database Initialization
```bash
psql -U postgres -c "DROP DATABASE IF EXISTS car_rental_recovery_test;"
psql -U postgres -c "CREATE DATABASE car_rental_recovery_test;"
```

### Step 3: Binary Dump Restoration
Execute `pg_restore` to rebuild schema objects, extensions (`btree_gist`, `pgcrypto`), indexes, and records:
```bash
pg_restore -U postgres -h localhost -p 5432 -d car_rental_recovery_test -v /backups/car_rental_backup.dump
```

---

## 3. Recovery Result Summary
- **Restoration Status:** SUCCESSFUL
- **Constraint Verification:** All relational foreign keys, check constraints, and the GiST exclusion constraint (`excl_rental_no_overlap`) successfully restored without error.
- **Data Completeness:** 100% record parity verified across all 17 tables and audit logs.
