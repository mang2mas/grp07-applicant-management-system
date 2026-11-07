# Activity 6: Database Backup Plan
**System:** Car Rental Management System (Group 06)  
**DBMS:** PostgreSQL 16  
**Target Database:** `car_rental_db`

---

## 1. Overview & Objectives
This document defines the official backup strategy and operational recovery procedures for the Car Rental Management System. The core objective is to ensure high availability, auditability, and data integrity for vehicle fleets, customer profiles, booking schedules, driver licenses, inspections, damage dispute resolutions, and payment ledgers.

---

## 2. Backup Strategy & Methods

### 2.1 Compressed Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Full disaster recovery and rapid database restoration.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d car_rental_db -F c -b -v -f /backups/car_rental_backup.dump
  ```

### 2.2 Plain Text Schema & Data Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Version-controlled schema migrations, code reviews, and developer environment seeding.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d car_rental_db -F p -v -f /backups/car_rental_backup.sql
  ```

---

## 3. Targeted Schema Objects
The backup routine captures all core database relations, indices, triggers, and extensions:
1. **Core Fleet & Catalog:** `vehicle_category`, `vehicle`, `rental_rate`, `vehicle_maintenance`
2. **Users & Authentication:** `customer`, `staff`, `driver_license`, `rental_terms`
3. **Operations & Bookings:** `rental`, `cancellation`, `vehicle_inspection`, `inspection_item`, `inspection_photo`
4. **Financials & Disputes:** `payment`, `damage_incident`, `damage_fee`
5. **Governance & Audit:** `audit_log`, database extensions (`pgcrypto`, `btree_gist`), and custom partial unique indexes / GiST exclusion constraints (`excl_rental_no_overlap`).

---

## 4. Automation & Retention Policy
- **Retention Schedule:** Retain 30 daily custom binary dumps and 12 weekly plain-text SQL files.
- **Cloud Replication:** Backup archives are encrypted and synchronized to secure off-site cloud storage.
- **Automated Cleanup:** Local backup files older than 30 days are automatically purged via scheduled cron jobs.
