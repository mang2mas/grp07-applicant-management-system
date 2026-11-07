# Activity 6: Database Backup Plan
**System:** Catering Business Management System (Group 08)  
**DBMS:** PostgreSQL 18.6  
**Target Database:** `catering_management_db`

---

## 1. Overview & Objectives
This document defines the official backup strategy and disaster recovery operational procedures for the Catering Business Management System. The primary goal is to protect customer records, catering packages, menu inventories, event reservations, staff assignments, payment transactions, and cancellation logs against data loss or hardware corruption.

---

## 2. Backup Strategy & Methods

### 2.1 Compressed Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Full disaster recovery and rapid database restoration.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d catering_management_db -F c -b -v -f /backups/catering_backup.dump
  ```

### 2.2 Plain Text Schema & Data Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Schema version tracking, code reviews, and staging environment seeding.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d catering_management_db -F p -v -f /backups/catering_backup.sql
  ```

---

## 3. Targeted Schema Objects
The backup protocol captures all relational tables, constraints, sequences, and security group roles:
1. **Customer & Staff Accounts:** `customer`, `employee`
2. **Catalog & Inventory:** `package`, `menu_item`, `inventory`
3. **Reservations & Operations:** `reservation`, `reservation_menu`, `event_assignment`, `cancellation`
4. **Financial Ledgers:** `payment`
5. **Access Control Roles:** `app_admin_grp`, `app_runtime_grp`, `app_readonly_grp`, `catering_app`

---

## 4. Automation & Retention Policy
- **Retention Schedule:** Maintain 30 daily compressed binary archives and 12 weekly plain-text SQL files.
- **Cloud Replication:** Encrypted daily backups are replicated to off-site cloud storage.
- **Purge Routine:** Automated cron jobs delete local backup files older than 30 days.
