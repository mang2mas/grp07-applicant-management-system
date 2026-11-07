-- Activity 6: Data Integrity & Recovery Verification Script
-- System: Gym Management System (Group 09)
-- Target DB: gym_recovery_test

\echo '======================================================='
\echo '  1. RECORD COUNT INTEGRITY CHECK'
\echo '======================================================='
SELECT 'trainers' AS table_name, COUNT(*) AS total_records FROM trainers
UNION ALL
SELECT 'members', COUNT(*) FROM members
UNION ALL
SELECT 'membership_plans', COUNT(*) FROM membership_plans
UNION ALL
SELECT 'memberships', COUNT(*) FROM memberships
UNION ALL
SELECT 'payments', COUNT(*) FROM payments
UNION ALL
SELECT 'classes', COUNT(*) FROM classes
UNION ALL
SELECT 'class_enrollments', COUNT(*) FROM class_enrollments
UNION ALL
SELECT 'attendance', COUNT(*) FROM attendance;

\echo '======================================================='
\echo '  2. ACTIVE MEMBERSHIP UNIQUE CONSTRAINT CHECK'
\echo '======================================================='
-- Verify that no member has more than one 'Active' membership
SELECT member_id, COUNT(*) AS active_count
FROM memberships
WHERE status = 'Active'
GROUP BY member_id
HAVING COUNT(*) > 1;

\echo '======================================================='
\echo '  3. MEMBER & TRAINER RELATIONSHIP CHECK'
\echo '======================================================='
SELECT 
    m.member_id,
    m.first_name || ' ' || m.last_name AS member_name,
    m.status AS member_status,
    COALESCE(t.first_name || ' ' || t.last_name, 'No Assigned Trainer') AS assigned_trainer
FROM members m
LEFT JOIN trainers t ON m.trainer_id = t.trainer_id;

\echo '======================================================='
\echo '  4. PAYMENT LEDGER RECONCILIATION'
\echo '======================================================='
SELECT 
    p.payment_id,
    p.membership_id,
    p.amount,
    p.payment_date,
    p.payment_method,
    ms.status AS membership_status
FROM payments p
JOIN memberships ms ON p.membership_id = ms.membership_id;

\echo '======================================================='
\echo '  5. CLASS ENROLLMENT & ATTENDANCE AUDIT'
\echo '======================================================='
SELECT 
    c.class_name,
    c.room,
    c.class_date,
    COUNT(ce.member_id) AS enrolled_members
FROM classes c
LEFT JOIN class_enrollments ce ON c.class_id = ce.class_id
GROUP BY c.class_id, c.class_name, c.room, c.class_date;
