# Activity 6: Database Backup Plan
**System:** ReserveIT Facility & Equipment Reservation System (Group 11)  
**DBMS:** PostgreSQL 16+ (Target PostgreSQL 18)  
**Target Database:** `reserveit_db`

---

## 1. Overview & Objectives
This document establishes the official backup policy and operational disaster recovery framework for ReserveIT. The primary objective is to safeguard university departments, user accounts, staff credentials, rooms, equipment inventories, reservations, material allocations, and penalty ledgers against hardware failures, corruption, or data loss.

---

## 2. Backup Strategy & Methods

### 2.1 Compressed Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Full database disaster recovery and rapid point-in-time restoration.
- **Command:**
  ```bash
  pg_dump -U reserveit_owner -h localhost -p 5432 -d reserveit_db -F c -b -v -f /backups/reserveit_backup.dump
  ```

### 2.2 Plain Text Schema & Data Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Version-controlled schema migrations, code reviews, and staging environment setup.
- **Command:**
  ```bash
  pg_dump -U reserveit_owner -h localhost -p 5432 -d reserveit_db -F p -v -f /backups/reserveit_backup.sql
  ```

---

## 3. Targeted Schema Objects
The backup protocol captures all relational tables, constraints, sequences, extensions, and security roles:
1. **Lookup & User Management:** `department`, `app_user`, `staff`
2. **Facility & Resource Catalogs:** `room`, `material`
3. **Reservations & Operations:** `reservation`, `reservation_material`, `penalty`
4. **Extensions & Constraints:** `btree_gist` extension and the GiST exclusion constraint (`no_overlapping_approved_reservations`).

---

## 4. Automation & Retention Policy
- **Retention Schedule:** Maintain 30 daily compressed binary archives and 12 weekly plain-text SQL files.
- **Cloud Replication:** Encrypted daily backups are synchronized to off-site cloud storage.
- **Purge Routine:** Automated cron jobs purge local backup files older than 30 days.
