-- =====================================================================
-- DropBoxed (Group 01) - Activity 6: Database Integrity Verification
-- 
-- Run this script against the restored test database (dropboxed_test_db)
-- to verify table existence, record counts, foreign key constraints,
-- sequence identity states, and role access permissions post-restoration.
--
-- Usage:
--   psql -U postgres -d dropboxed_test_db -f verification_queries.sql
-- =====================================================================

SET search_path TO dropboxed;

-- ---------------------------------------------------------------------
-- 1. Schema & Table Structure Check
-- Verify that all 8 core tables exist in the dropboxed schema.
-- ---------------------------------------------------------------------
SELECT '--- 1. SCHEMA & TABLE EXISTENCE CHECK ---' AS verification_step;

SELECT 
    table_name,
    CASE 
        WHEN table_name IN ('users', 'staff', 'complaint_categories', 'locations', 'complaints', 'complaint_assignments', 'status_logs', 'attachments') THEN 'EXISTS'
        ELSE 'UNKNOWN'
    END AS status
FROM information_schema.tables
WHERE table_schema = 'dropboxed'
ORDER BY table_name;

-- ---------------------------------------------------------------------
-- 2. Table Record Count Audit
-- Compares live table row counts post-restore.
-- ---------------------------------------------------------------------
SELECT '--- 2. RECORD COUNT AUDIT ---' AS verification_step;

SELECT 'users' AS table_name, COUNT(*) AS row_count FROM dropboxed.users
UNION ALL
SELECT 'staff' AS table_name, COUNT(*) AS row_count FROM dropboxed.staff
UNION ALL
SELECT 'complaint_categories' AS table_name, COUNT(*) AS row_count FROM dropboxed.complaint_categories
UNION ALL
SELECT 'locations' AS table_name, COUNT(*) AS row_count FROM dropboxed.locations
UNION ALL
SELECT 'complaints' AS table_name, COUNT(*) AS row_count FROM dropboxed.complaints
UNION ALL
SELECT 'complaint_assignments' AS table_name, COUNT(*) AS row_count FROM dropboxed.complaint_assignments
UNION ALL
SELECT 'status_logs' AS table_name, COUNT(*) AS row_count FROM dropboxed.status_logs
UNION ALL
SELECT 'attachments' AS table_name, COUNT(*) AS row_count FROM dropboxed.attachments;

-- ---------------------------------------------------------------------
-- 3. Referential Integrity Check (Orphan Foreign Key Scan)
-- Ensure no orphan records exist across child tables.
-- ---------------------------------------------------------------------
SELECT '--- 3. REFERENTIAL INTEGRITY AUDIT ---' AS verification_step;

-- Check orphan complaints (invalid user, category, or location)
SELECT 'Orphan Complaints (user_id)' AS check_item, COUNT(*) AS orphan_count 
FROM dropboxed.complaints c 
LEFT JOIN dropboxed.users u ON c.user_id = u.user_id 
WHERE u.user_id IS NULL

UNION ALL

SELECT 'Orphan Complaints (category_id)', COUNT(*) 
FROM dropboxed.complaints c 
LEFT JOIN dropboxed.complaint_categories cat ON c.category_id = cat.category_id 
WHERE cat.category_id IS NULL

UNION ALL

SELECT 'Orphan Complaints (location_id)', COUNT(*) 
FROM dropboxed.complaints c 
LEFT JOIN dropboxed.locations l ON c.location_id = l.location_id 
WHERE l.location_id IS NULL

UNION ALL

-- Check orphan complaint assignments
SELECT 'Orphan Assignments (complaint_id)', COUNT(*) 
FROM dropboxed.complaint_assignments ca 
LEFT JOIN dropboxed.complaints c ON ca.complaint_id = c.complaint_id 
WHERE c.complaint_id IS NULL

UNION ALL

-- Check orphan status logs
SELECT 'Orphan Status Logs (complaint_id)', COUNT(*) 
FROM dropboxed.status_logs sl 
LEFT JOIN dropboxed.complaints c ON sl.complaint_id = c.complaint_id 
WHERE c.complaint_id IS NULL

UNION ALL

-- Check orphan attachments
SELECT 'Orphan Attachments (complaint_id)', COUNT(*) 
FROM dropboxed.attachments a 
LEFT JOIN dropboxed.complaints c ON a.complaint_id = c.complaint_id 
WHERE c.complaint_id IS NULL;

-- ---------------------------------------------------------------------
-- 4. Identity Sequence Alignment Check
-- Verify identity sequence max values to prevent primary key collision.
-- ---------------------------------------------------------------------
SELECT '--- 4. IDENTITY SEQUENCE AUDIT ---' AS verification_step;

SELECT 
    table_name,
    column_name,
    is_identity
FROM information_schema.columns
WHERE table_schema = 'dropboxed' AND is_identity = 'YES'
ORDER BY table_name;

-- ---------------------------------------------------------------------
-- 5. Role Privileges & ACL Verification
-- Validate that least-privilege role memberships and grants are active.
-- ---------------------------------------------------------------------
SELECT '--- 5. ROLE PRIVILEGE & ACL AUDIT ---' AS verification_step;

SELECT 
    grantee, 
    table_name, 
    privilege_type 
FROM information_schema.role_table_grants 
WHERE table_schema = 'dropboxed' 
  AND grantee IN ('dropboxed_dba_grp', 'dropboxed_runtime_grp', 'dropboxed_readonly_grp', 'dropboxed_backup_grp')
ORDER BY grantee, table_name, privilege_type;
