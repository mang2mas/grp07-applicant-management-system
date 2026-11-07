# Activity 6: Database Recovery Execution Report
**System:** Shiori Cafe Management System (Group 05)  
**DBMS:** PostgreSQL 16  
**Target Recovery Database:** `shioricafe_recovery_test` (Schema: `shioricafe_schema`)

---

## 1. Executive Summary
This report documents the disaster recovery drill conducted for the Shiori Cafe database. A simulated service disruption was resolved by restoring the production binary dump `shioricafe_backup.dump` into an isolated test environment (`shioricafe_recovery_test`).

---

## 2. Step-by-Step Recovery Protocol

### Step 1: Active Connection Termination & Isolation
Terminate existing client connections to the recovery database prior to re-creation:
```sql
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'shioricafe_recovery_test';
```

### Step 2: Database Re-creation
```bash
psql -U postgres -c "DROP DATABASE IF EXISTS shioricafe_recovery_test;"
psql -U postgres -c "CREATE DATABASE shioricafe_recovery_test;"
```

### Step 3: Binary Restoration
Execute `pg_restore` targeting the isolated schema and relations:
```bash
pg_restore -U postgres -h localhost -p 5432 -d shioricafe_recovery_test -v /backups/shioricafe_backup.dump
```

---

## 3. Recovery Result Summary
- **Restoration Status:** SUCCESSFUL
- **Schema Validation:** `shioricafe_schema` namespace, table structures (`table`, `category`, `menu_item`), and primary key constraints successfully rebuilt.
- **Data Completeness:** 100% verified row count match across all tables.
