# Activity 6: Database Recovery Execution Report
**System:** ReserveIT Facility & Equipment Reservation System (Group 11)  
**DBMS:** PostgreSQL 16+ (Target PostgreSQL 18)  
**Target Recovery Database:** `reserveit_recovery_test`

---

## 1. Executive Summary
This report documents the disaster recovery verification procedure performed for ReserveIT. A simulated restoration test was conducted by restoring the compressed binary backup `reserveit_backup.dump` into an isolated verification environment (`reserveit_recovery_test`).

---

## 2. Step-by-Step Recovery Protocol

### Step 1: Active Connection Termination & Isolation
Force-close any active client connections to the recovery database prior to re-creation:
```sql
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'reserveit_recovery_test';
```

### Step 2: Database Initialization
```bash
psql -U postgres -c "DROP DATABASE IF EXISTS reserveit_recovery_test;"
psql -U postgres -c "CREATE DATABASE reserveit_recovery_test WITH OWNER reserveit_owner;"
```

### Step 3: Binary Dump Restoration
Execute `pg_restore` to restore the schema, extensions (`btree_gist`), tables, foreign keys, GiST exclusion constraints, sequences, and seed data:
```bash
pg_restore -U reserveit_owner -h localhost -p 5432 -d reserveit_recovery_test -v /backups/reserveit_backup.dump
```

---

## 3. Recovery Result Summary
- **Restoration Status:** SUCCESSFUL
- **Integrity Validation:** All foreign key constraints, check constraints (`capacity > 0`, `end_time > start_time`, `available_quantity BETWEEN 0 AND total_quantity`), and the GiST exclusion constraint (`no_overlapping_approved_reservations`) were successfully restored and validated.
- **Data Completeness:** 100% record match verified across all 8 base tables and serial sequences.
