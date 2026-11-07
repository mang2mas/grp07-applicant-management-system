# Database Backup Plan — Group 02 (School Event Management System)

## 1. Executive Summary & Objectives
This backup plan defines the operational procedures, schedule, and recovery metrics for the `school_event_db` PostgreSQL database powering the School Event Management System. It guarantees high availability, disaster readiness, zero data loss, and point-in-time snapshot recovery for events, schedules, venue bookings, participant registrations, attendance logs, and system audit trails.

### Core Recovery Metrics
* **Recovery Point Objective (RPO):** < 1 Hour (maximum allowable data loss window).
* **Recovery Time Objective (RTO):** < 15 Minutes (target restoration window for critical system services).
* **Backup Format:** PostgreSQL Custom Format (`-Fc` / Compressed Binary Archive).

---

## 2. Database Scope & Target Environment
* **Database Name:** `school_event_db`
* **Target Schema:** `public`
* **Database Owner:** `postgres`
* **Dedicated Backup Role:** `school_event_backup` (Member of `school_event_backup_grp`)
* **Tables Covered (9):**
  1. `user_accounts`
  2. `roles`
  3. `venues`
  4. `events`
  5. `event_schedules`
  6. `participants`
  7. `event_assignments`
  8. `attendances`
  9. `audit_logs`

---

## 3. Least-Privilege Access Control & Backup Account Configuration
Backups are executed using a dedicated, restricted database login account `school_event_backup` to strictly avoid using superuser accounts for routine operational tasks.

* **Group Role:** `school_event_backup_grp`
* **Assigned Privileges:**
  - `CONNECT` privilege on database `school_event_db`.
  - `USAGE` privilege on schema `public`.
  - `SELECT` privilege on all current and future tables and sequences in schema `public`.
* **Security Constraints:** The backup role possesses read-only table/sequence permissions and cannot modify data, execute DDL statements, or alter cluster parameters.

---

## 4. Execution Commands & Automation Scripting

### 4.1 Logical Database Backup Execution
Execute the custom-format binary backup using `pg_dump`:

```bash
pg_dump -h localhost \
        -U school_event_backup \
        -d school_event_db \
        -F c \
        -b \
        -v \
        -f "./backups/school_event_db_$(date +%Y%m%d_%H%M%S).dump"
```

### 4.2 Cluster Roles & Global Objects Backup
Because single-database backups do not include cluster-wide roles, global database roles are exported separately using `pg_dumpall`:

```bash
pg_dumpall -h localhost \
           -U postgres \
           --roles-only \
           -f "./backups/school_event_roles_$(date +%Y%m%d).sql"
```

### 4.3 Cryptographic Integrity Checksum (SHA-256)
Immediately following backup generation, compute a SHA-256 hash to protect against file corruption or unauthorized modification:

```bash
sha256sum ./backups/school_event_db_*.dump > ./backups/school_event_db_latest.dump.sha256
```

---

## 5. Retention Schedule & Disaster Recovery Policy
1. **Retention Windows:**
   * Daily custom backups retained locally for 14 days.
   * Weekly full database snapshots retained for 8 weeks.
   * Monthly system backups archived securely for 12 months.
2. **Automated Cron Schedule (`crontab`):**
   ```cron
   # Daily automated database dump at 02:00 AM
   0 2 * * * postgres /usr/local/bin/backup_school_event.sh >> /var/log/school_event_backup.log 2>&1
   ```
