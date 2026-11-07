-- Activity 6: Data Integrity & Recovery Verification Script
-- System: UniSync Event & Organization Management System (Group 10)
-- Target DB: unisync_recovery_test

\echo '======================================================='
\echo '  1. RECORD COUNT INTEGRITY CHECK'
\echo '======================================================='
SELECT 'account' AS table_name, COUNT(*) AS total_records FROM account
UNION ALL
SELECT 'organization', COUNT(*) FROM organization
UNION ALL
SELECT 'membership', COUNT(*) FROM membership
UNION ALL
SELECT 'event', COUNT(*) FROM event
UNION ALL
SELECT 'venue', COUNT(*) FROM venue
UNION ALL
SELECT 'event_schedule', COUNT(*) FROM event_schedule
UNION ALL
SELECT 'registration', COUNT(*) FROM registration
UNION ALL
SELECT 'notification', COUNT(*) FROM notification;

\echo '======================================================='
\echo '  2. MEMBERSHIP ROLE & UNIQUE CONSTRAINT AUDIT'
\echo '======================================================='
-- Verify that unique organization-account pairs are respected
SELECT account_id, organization_id, COUNT(*) AS duplicate_count
FROM membership
GROUP BY account_id, organization_id
HAVING COUNT(*) > 1;

\echo '======================================================='
\echo '  3. EVENT SCHEDULE OVERLAP / TIME INTEGRITY CHECK'
\echo '======================================================='
-- Verify check constraint enforcement (end_time > start_time)
SELECT schedule_id, event_id, venue_id, event_date, start_time, end_time
FROM event_schedule
WHERE end_time <= start_time;

\echo '======================================================='
\echo '  4. VENUE COORDINATE BOUNDS VERIFICATION'
\echo '======================================================='
SELECT venue_id, venue_name, latitude, longitude
FROM venue
WHERE (latitude IS NOT NULL AND (latitude < -90 OR latitude > 90))
   OR (longitude IS NOT NULL AND (longitude < -180 OR longitude > 180));

\echo '======================================================='
\echo '  5. EVENT REGISTRATION STATUS RECONCILIATION'
\echo '======================================================='
SELECT 
    e.event_name,
    COUNT(r.registration_id) AS total_registrations,
    SUM(CASE WHEN r.status = 'active' THEN 1 ELSE 0 END) AS active_registrations
FROM event e
LEFT JOIN registration r ON e.event_id = r.event_id
GROUP BY e.event_id, e.event_name
ORDER BY total_registrations DESC
LIMIT 5;
