# Activity 6: Database Recovery Execution Report
**System:** Pawpaw Clinic Management System (Group 03)  
**DBMS:** PostgreSQL 16  
**Target Recovery Database:** `pawpaw_clinic_recovery_test`

---

## 1. Executive Summary
This report documents the verification and disaster recovery procedure performed on the Pawpaw Clinic database. A simulated failure scenario was conducted by restoring the production dump `pawpaw_clinic_backup.dump` into an isolated target environment (`pawpaw_clinic_recovery_test`).

---

## 2. Step-by-Step Disaster Recovery Protocol

### Step 1: Environment Isolation & Active Connection Termination
Before executing the recovery pipeline, existing active connections to the target test database are forcibly closed:
```sql
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'pawpaw_clinic_recovery_test';
```

### Step 2: Database Re-creation
```bash
psql -U postgres -c "DROP DATABASE IF EXISTS pawpaw_clinic_recovery_test;"
psql -U postgres -c "CREATE DATABASE pawpaw_clinic_recovery_test;"
```

### Step 3: Binary Dump Restoration
Restoration executed using `pg_restore` targeting the isolated database:
```bash
pg_restore -U postgres -h localhost -p 5432 -d pawpaw_clinic_recovery_test -v /backups/pawpaw_clinic_backup.dump
```

---

## 3. Recovery Result Summary
- **Restoration Status:** SUCCESSFUL
- **Total Executed Statements:** Clean execution of table structures, functions, triggers, and foreign keys.
- **Trigger Integrity:** `trg_appointment_service_price` function restored and verified functional.
- **Data Completeness:** 100% of rows restored across all 12 relation tables.
