# Activity 6: Database Backup Plan
**System:** HealthBridge Clinic Management System (Group 04)  
**DBMS:** PostgreSQL 16  
**Target Database:** `grp03_projectdb`

---

## 1. Overview & Objectives
This document defines the official database backup procedures and disaster recovery strategies for the HealthBridge Clinic Management System. The primary objective is to maintain daily, high-availability backups to protect patient identity data, clinical observations, appointment scheduling slots, billing transactions, and access log data from hardware failures or system corruption.

---

## 2. Backup Strategy & Methods

### 2.1 Compressed Binary Backup (`pg_dump` Custom Format)
- **Frequency:** Daily at midnight (00:00 UTC)
- **Format:** Custom PostgreSQL Compressed Format (`-F c`)
- **Use Case:** Full disaster recovery and rapid database restoration.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d grp03_projectdb -F c -b -v -f /backups/healthbridge_backup.dump
  ```

### 2.2 Plain Text Schema & Data Dump
- **Frequency:** Weekly (Sunday at 01:00 UTC)
- **Format:** Plain Text SQL (`-F p`)
- **Use Case:** Version-controlled schema auditing and developer environment seeding.
- **Command:**
  ```bash
  pg_dump -U postgres -h localhost -p 5432 -d grp03_projectdb -F p -v -f /backups/healthbridge_backup.sql
  ```

---

## 3. Targeted Schema Objects
The backup pipeline captures the complete set of relations and security settings:
1. **Reference Data:** `specialties`, `doctors`
2. **Clinical Data & Workflow:** `patients`, `appointments`, `medical_notes`, `prescriptions`, `billing`
3. **Identity & Access Management:** `staff_users`, `staff_password_resets`, `patient_password_resets`
4. **Audit & Recovery Staging:** `deleted_records`
5. **Database Views & Indexes:** `appointment_details`, `visit_records`, `appointments_doctor_slot`, `patients_email_unique`

---

## 4. Automation & Retention Policy
- **Retention Schedule:** Retain 30 daily custom binary dumps and 12 weekly plain-text SQL files.
- **Off-Site Replication:** Backup files are encrypted and synced to secure off-site cloud storage post-execution.
- **Cleanup Routine:** Automated cron jobs purge local backup archives older than 30 days.
