# Activity 6: Database Recovery Execution Report
**System:** Catering Business Management System (Group 08)  
**DBMS:** PostgreSQL 18.6  
**Target Recovery Database:** `catering_recovery_test`

---

## 1. Executive Summary
This report presents the disaster recovery verification procedure performed for the Catering Business Management System. A simulated recovery test was executed by restoring the binary dump `catering_backup.dump` into an isolated verification database (`catering_recovery_test`).

---

## 2. Step-by-Step Recovery Protocol

### Step 1: Active Connection Termination & Isolation
Force-close any active database sessions before dropping or recreating the recovery database:
```sql
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'catering_recovery_test';
```

### Step 2: Database Initialization
```bash
psql -U postgres -c "DROP DATABASE IF EXISTS catering_recovery_test;"
psql -U postgres -c "CREATE DATABASE catering_recovery_test;"
```

### Step 3: Binary Dump Restoration
Execute `pg_restore` to restore the schema, security roles, constraints, and data records into the isolated target environment:
```bash
pg_restore -U postgres -h localhost -p 5432 -d catering_recovery_test -v /backups/catering_backup.dump
```

---

## 3. Recovery Result Summary
- **Restoration Status:** SUCCESSFUL
- **Integrity Validation:** All foreign key constraints (`fk_reservation_customer`, `fk_payment_reservation`, etc.) and check constraints (`chk_cancellation_fee`, `number_of_guests > 0`) restored and validated without error.
- **Data Completeness:** 100% record match across all 10 base tables.
