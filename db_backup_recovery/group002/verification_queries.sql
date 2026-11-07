-- =====================================================================
-- School Event Management System (Group 02) - Activity 6 Verification
-- 
-- Run this script against the restored test database (school_event_test_db)
-- to verify table structures, record counts, referential constraints,
-- sequence identity states, and role access privileges post-restoration.
--
-- Usage:
--   psql -U postgres -d school_event_test_db -f verification_queries.sql
-- =====================================================================

SET search_path TO public;

-- ---------------------------------------------------------------------
-- 1. Schema & Table Structure Existence Check
-- Verify all 9 primary tables exist in the public schema.
-- ---------------------------------------------------------------------
SELECT '--- 1. SCHEMA & TABLE STRUCTURE CHECK ---' AS verification_step;

SELECT 
    table_name,
    CASE 
        WHEN table_name IN ('user_accounts', 'roles', 'venues', 'events', 'event_schedules', 'participants', 'event_assignments', 'attendances', 'audit_logs') THEN 'EXISTS'
        ELSE 'UNKNOWN'
    END AS status
FROM information_schema.tables
WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
ORDER BY table_name;

-- ---------------------------------------------------------------------
-- 2. Table Record Count Audit
-- Audit live row counts across all system entities.
-- ---------------------------------------------------------------------
SELECT '--- 2. TABLE RECORD COUNT AUDIT ---' AS verification_step;

SELECT 'user_accounts' AS table_name, COUNT(*) AS row_count FROM public.user_accounts
UNION ALL
SELECT 'roles', COUNT(*) FROM public.roles
UNION ALL
SELECT 'venues', COUNT(*) FROM public.venues
UNION ALL
SELECT 'events', COUNT(*) FROM public.events
UNION ALL
SELECT 'event_schedules', COUNT(*) FROM public.event_schedules
UNION ALL
SELECT 'participants', COUNT(*) FROM public.participants
UNION ALL
SELECT 'event_assignments', COUNT(*) FROM public.event_assignments
UNION ALL
SELECT 'attendances', COUNT(*) FROM public.attendances
UNION ALL
SELECT 'audit_logs', COUNT(*) FROM public.audit_logs;

-- ---------------------------------------------------------------------
-- 3. Referential Integrity Check (Orphan Foreign Key Scan)
-- Ensure zero orphan records exist across relationship links.
-- ---------------------------------------------------------------------
SELECT '--- 3. REFERENTIAL INTEGRITY SCAN ---' AS verification_step;

-- Orphan check for events -> venues / users
SELECT 'Orphan Events (venue_id)' AS check_item, COUNT(*) AS orphan_count 
FROM public.events e 
LEFT JOIN public.venues v ON e.venue_id = v.venue_id 
WHERE v.venue_id IS NULL AND e.venue_id IS NOT NULL

UNION ALL

SELECT 'Orphan Events (created_by)', COUNT(*) 
FROM public.events e 
LEFT JOIN public.user_accounts u ON e.created_by = u.user_id 
WHERE u.user_id IS NULL AND e.created_by IS NOT NULL

UNION ALL

-- Orphan check for event schedules -> events
SELECT 'Orphan Schedules (event_id)', COUNT(*) 
FROM public.event_schedules es 
LEFT JOIN public.events e ON es.event_id = e.event_id 
WHERE e.event_id IS NULL

UNION ALL

-- Orphan check for event assignments -> events / users
SELECT 'Orphan Assignments (event_id)', COUNT(*) 
FROM public.event_assignments ea 
LEFT JOIN public.events e ON ea.event_id = e.event_id 
WHERE e.event_id IS NULL

UNION ALL

SELECT 'Orphan Assignments (staff_id)', COUNT(*) 
FROM public.event_assignments ea 
LEFT JOIN public.user_accounts u ON ea.staff_id = u.user_id 
WHERE u.user_id IS NULL

UNION ALL

-- Orphan check for attendances -> events / participants
SELECT 'Orphan Attendances (event_id)', COUNT(*) 
FROM public.attendances a 
LEFT JOIN public.events e ON a.event_id = e.event_id 
WHERE e.event_id IS NULL

UNION ALL

SELECT 'Orphan Attendances (participant_id)', COUNT(*) 
FROM public.attendances a 
LEFT JOIN public.participants p ON a.participant_id = p.participant_id 
WHERE p.participant_id IS NULL

UNION ALL

-- Orphan check for audit logs -> users
SELECT 'Orphan Audit Logs (user_id)', COUNT(*) 
FROM public.audit_logs al 
LEFT JOIN public.user_accounts u ON al.user_id = u.user_id 
WHERE u.user_id IS NULL AND al.user_id IS NOT NULL;

-- ---------------------------------------------------------------------
-- 4. Primary Key & Identity Sequence Alignment Check
-- Verify identity sequences to prevent primary key key collision.
-- ---------------------------------------------------------------------
SELECT '--- 4. SEQUENCE & PRIMARY KEY IDENTITY AUDIT ---' AS verification_step;

SELECT 
    table_name,
    column_name,
    column_default,
    is_identity
FROM information_schema.columns
WHERE table_schema = 'public' 
  AND (is_identity = 'YES' OR column_default LIKE 'nextval%')
ORDER BY table_name;

-- ---------------------------------------------------------------------
-- 5. Database Roles & Privilege Access Control Audit
-- Validate that least-privilege group grants remain active.
-- ---------------------------------------------------------------------
SELECT '--- 5. ROLE & PRIVILEGE ACL AUDIT ---' AS verification_step;

SELECT 
    grantee, 
    table_name, 
    privilege_type 
FROM information_schema.role_table_grants 
WHERE table_schema = 'public' 
ORDER BY grantee, table_name, privilege_type;
