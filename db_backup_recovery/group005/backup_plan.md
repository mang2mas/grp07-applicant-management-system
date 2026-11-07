# Activity 6: Database Backup Plan
**System:** Shiori Cafe Management System (Group 05)  
**DBMS:** PostgreSQL 16  
**Target Database:** `grp10_shioricafedb` (Schema: `shioricafe_schema`)

---

## 1. Overview & Objectives
This document details the automated backup schedule and disaster recovery protocol for the Shiori Cafe Management System. The objective is to safeguard customer table reservations, menu inventories, seating configurations, order items, and POS transaction records against data loss or corruption.

---

## 2. Backup Strategy & Methods

### 2.1 Logical Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Complete database restoration and emergency disaster recovery.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d grp10_shioricafedb -F c -b -v -f /backups/shioricafe_backup.dump
  ```

### 2.2 Plain Text Schema & Data Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Schema version control, differential analysis, and offline code reviews.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d grp10_shioricafedb -F p -v -f /backups/shioricafe_backup.sql
  ```

---

## 3. Targeted Schema Objects
The backup protocol captures all core relations under the `shioricafe_schema` namespace:
1. **Seeding & Inventory:** `shioricafe_schema.table`
2. **Catalog & Menu:** `shioricafe_schema.category`, `shioricafe_schema.menu_item`
3. **Operational Tables:** Customer reservations, POS orders, transaction logs, and user access records.

---

## 4. Automation & Retention Policy
- **Retention Period:** Maintain 30 daily compressed binary archives and 12 weekly plain-text SQL files.
- **Cloud Synchronization:** Secure encrypted replication to off-site cloud storage immediately following local generation.
- **Purge Schedule:** Automated clean-up scripts remove local backups older than 30 days.
