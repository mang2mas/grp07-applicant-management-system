# Activity 6: Database Backup Plan
**System:** UniSync Event & Organization Management System (Group 10)  
**DBMS:** PostgreSQL 16  
**Target Database:** `unisync_db`

---

## 1. Overview & Objectives
This document details the backup strategy and disaster recovery operational policy for the UniSync platform. The primary objective is to protect user accounts, university student organizations, administrative memberships, event schedules, venue reservations, registration logs, and system notifications against accidental data loss, corruption, or hardware failure.

---

## 2. Backup Strategy & Methods

### 2.1 Compressed Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Full disaster recovery and rapid database restoration.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d unisync_db -F c -b -v -f /backups/unisync_backup.dump
  ```

### 2.2 Plain Text Schema & Data Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Version-controlled schema migrations, code audits, and development environment setup.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d unisync_db -F p -v -f /backups/unisync_backup.sql
  ```

---

## 3. Targeted Schema Objects
The backup routine captures all relational tables, constraints, sequences, and indexes:
1. **Core Accounts & Organizations:** `account`, `organization`, `membership`
2. **Events & Venues:** `event`, `venue`, `event_schedule`
3. **Operations & Tracking:** `registration`, `notification`
4. **Optimizations:** All btree and compound indexes (`idx_membership_organization`, `idx_schedule_date_venue`, etc.)

---

## 4. Automation & Retention Policy
- **Retention Schedule:** Retain 30 daily custom binary backup dumps and 12 weekly plain-text SQL files.
- **Cloud Replication:** Backup archives are encrypted and synchronized to secure off-site cloud storage.
- **Automated Cleanup:** Local backup files older than 30 days are automatically purged via cron jobs.
