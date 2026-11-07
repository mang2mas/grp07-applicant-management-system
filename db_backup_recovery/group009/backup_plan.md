# Activity 6: Database Backup Plan
**System:** Gym Management System (Group 09)  
**DBMS:** PostgreSQL 16  
**Target Database:** `gym_management_db`

---

## 1. Overview & Objectives
This document establishes the official backup policy and operational disaster recovery framework for the Gym Management System. The primary objective is to protect trainer records, member profiles, membership subscriptions, payment ledgers, fitness classes, class enrollments, and check-in attendance tracking against system failures or data corruption.

---

## 2. Backup Strategy & Methods

### 2.1 Compressed Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Full database disaster recovery and rapid point-in-time restoration.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d gym_management_db -F c -b -v -f /backups/gym_backup.dump
  ```

### 2.2 Plain Text Schema & Data Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Version-controlled schema migrations, code audits, and development environment setup.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d gym_management_db -F p -v -f /backups/gym_backup.sql
  ```

---

## 3. Targeted Schema Objects
The backup protocol captures all relational tables, constraints, sequences, and partial unique indexes:
1. **Trainers & Members:** `trainers`, `members`
2. **Subscriptions & Financials:** `membership_plans`, `memberships`, `payments`
3. **Classes & Scheduling:** `classes`, `class_enrollments`, `attendance`
4. **Governance & Constraints:** Partial unique index enforcing single active memberships (`unique_active_membership`).

---

## 4. Automation & Retention Policy
- **Retention Schedule:** Maintain 30 daily compressed binary archives and 12 weekly plain-text SQL files.
- **Cloud Replication:** Encrypted daily backups are synced to off-site cloud storage.
- **Purge Routine:** Automated cron jobs purge local backup files older than 30 days.
