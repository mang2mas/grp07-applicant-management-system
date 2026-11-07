# Database Backup Plan — Group 01 (DropBoxed System)

## 1. Executive Summary & Objectives
This backup plan defines the operational procedures, schedule, and recovery metrics for the `dropboxed_db` PostgreSQL database. It ensures continuous availability, disaster readiness, and zero data loss for water quality complaint records, locations, resident accounts, and status audit logs.

### Core Recovery Objectives
* **Recovery Point Objective (RPO):** < 1 Hour (maximum allowable data loss window).
* **Recovery Time Objective (RTO):** < 15 Minutes (target restoration window for critical services).
* **Backup Format:** PostgreSQL Custom Format (`-Fc` / Compressed Binary Archive).

---

## 2. Database Scope & Target Environment
* **Database Name:** `dropboxed_db`
* **Target Schema:** `dropboxed`
* **Database Owner:** `postgres`
* **Backup Role:** `dropboxed_backup` (Member of `dropboxed_backup_grp`)
* **Tables Covered (8):**
  1. `users`
  2. `staff`
  3. `complaint_categories`
  4. `locations`
  5. `complaints`
  6. `complaint_assignments`
  7. `status_logs`
  8. `attachments`

---

## 3. Least-Privilege Backup Role Configuration
Backups are executed using the dedicated `dropboxed_backup` login account to avoid running operations as a superuser.

* **Group Role:** `dropboxed_backup_grp`
* **Granted Privileges:** `CONNECT` on `dropboxed_db`, `USAGE` on schema `dropboxed`, `SELECT` on all current and future tables and sequences in `dropboxed`.
* **Security Guardrail:** The backup role possesses read-only table/sequence access and cannot modify data, execute DDL, or bypass RLS.

---

## 4. Execution Commands & Automation Scripting

### 4.1 Logical Database Backup Execution
Run the custom-format dump as `dropboxed_backup`:

```bash
pg_dump -h localhost \
        -U dropboxed_backup \
        -d dropboxed_db \
        -F c \
        -b \
        -v \
        -f "./backups/dropboxed_db_$(date +%Y%m%d_%H%M%S).dump"
```

### 4.2 Cluster Roles & Global Objects Backup
Because `pg_dump` only backs up individual database objects and excludes global cluster roles, cluster roles must be dumped separately using `pg_dumpall`:

```bash
pg_dumpall -h localhost \
           -U postgres \
           --roles-only \
           -f "./backups/dropboxed_roles_$(date +%Y%m%d).sql"
```

### 4.3 Backup Integrity Verification (SHA-256)
Generate a cryptographic hash immediately after backup creation to guarantee artifact integrity:

```bash
sha256sum ./backups/dropboxed_db_*.dump > ./backups/dropboxed_db_latest.dump.sha256
```

---

## 5. Retention & Storage Policy
1. **Local Storage Policy:**
   * Daily backups retained for 14 days.
   * Weekly backups retained for 8 weeks.
   * Monthly full backups archived for 12 months.
2. **Automated Cron Schedule (`crontab`):**
   ```cron
   # Execute full custom-format database backup daily at 02:00 AM
   0 2 * * * postgres /usr/local/bin/backup_dropboxed.sh >> /var/log/db_backup.log 2>&1
   ```
