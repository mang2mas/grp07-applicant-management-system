# Activity 6: Database Backup Plan
**System:** Pawpaw Clinic Management System (Group 03)  
**DBMS:** PostgreSQL 16  
**Target Database:** `pawpaw_clinic_db`

---

## 1. Overview & Objectives
This document establishes the official backup strategy and operational procedures for the Pawpaw Clinic Management System database. The primary objective is to maintain daily point-in-time state backups to safeguard patient records, appointment histories, medical logs, and transaction audit trails against data corruption or hardware failure.

---

## 2. Backup Strategy & Methods

### 2.1 Logical Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Production disaster recovery, standard full database restores.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d pawpaw_clinic_db -F c -b -v -f /backups/pawpaw_clinic_backup.dump
  ```

### 2.2 Plain Text SQL Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Code audits, schema tracking, cross-platform migrations.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d pawpaw_clinic_db -F p -v -f /backups/pawpaw_clinic_backup.sql
  ```

---

## 3. Targeted Core Entities
The backup routine captures the following business-critical schema layers:
1. **User Access Control:** `USER_ACCOUNT`, `AUTH_SESSION`, `PASSWORD_RESET_CODE`
2. **Clinic Personnel & Clients:** `OWNER`, `VETERINARIAN`, `PET`
3. **Operational Services & Scheduling:** `SERVICE`, `APPOINTMENT`, `APPOINTMENT_SERVICE`
4. **Clinical Records:** `MEDICAL_RECORD`, `PRESCRIPTION`, `VACCINATION`
5. **System Governance:** `AUDIT_LOG`, `NOTIFICATION`

---

## 4. Automation & Retention Policy
- **Retention Period:** 30 daily custom dumps and 12 weekly plain-text SQL files are retained locally and replicated to off-site cloud storage.
- **Automated Cleanup:** Obsolete local backup files older than 30 days are automatically purged via cron execution.
