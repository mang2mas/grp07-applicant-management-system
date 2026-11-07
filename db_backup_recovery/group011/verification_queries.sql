-- Activity 6: Data Integrity & Recovery Verification Script
-- System: ReserveIT Facility & Equipment Reservation System (Group 11)
-- Target DB: reserveit_recovery_test

\echo '======================================================='
\echo '  1. RECORD COUNT INTEGRITY CHECK'
\echo '======================================================='
SELECT 'department' AS table_name, COUNT(*) AS total_records FROM department
UNION ALL
SELECT 'app_user', COUNT(*) FROM app_user
UNION ALL
SELECT 'staff', COUNT(*) FROM staff
UNION ALL
SELECT 'room', COUNT(*) FROM room
UNION ALL
SELECT 'material', COUNT(*) FROM material
UNION ALL
SELECT 'reservation', COUNT(*) FROM reservation
UNION ALL
SELECT 'reservation_material', COUNT(*) FROM reservation_material
UNION ALL
SELECT 'penalty', COUNT(*) FROM penalty;

\echo '======================================================='
\echo '  2. RESERVATION GIST OVERLAP CONSTRAINT AUDIT'
\echo '======================================================='
-- Verify that no two APPROVED reservations for the same room overlap in time
SELECT 
    r1.reservation_id AS booking_1,
    r2.reservation_id AS booking_2,
    r1.room_id,
    r1.reservation_date,
    r1.start_time AS b1_start,
    r1.end_time AS b1_end,
    r2.start_time AS b2_start,
    r2.end_time AS b2_end
FROM reservation r1
JOIN reservation r2 ON r1.room_id = r2.room_id AND r1.reservation_id < r2.reservation_id
WHERE r1.status = 'APPROVED'
  AND r2.status = 'APPROVED'
  AND r1.reservation_date = r2.reservation_date
  AND r1.start_time < r2.end_time AND r2.start_time < r1.end_time;

\echo '======================================================='
\echo '  3. MATERIAL AVAILABILITY CONSTRAINTS CHECK'
\echo '======================================================='
-- Verify that available quantities do not exceed total quantities
SELECT material_id, material_name, total_quantity, available_quantity
FROM material
WHERE available_quantity < 0 OR available_quantity > total_quantity;

\echo '======================================================='
\echo '  4. PENALTY LEDGER INTEGRITY CHECK'
\echo '======================================================='
SELECT 
    p.penalty_id,
    p.reservation_id,
    p.reason,
    p.amount,
    p.paid_status,
    p.paid_date
FROM penalty p;

\echo '======================================================='
\echo '  5. RESERVATION REVIEWER BUSINESS RULE AUDIT'
\echo '======================================================='
-- Business Rule 4: approved, rejected, or completed reservations must have a staff reviewer
SELECT reservation_id, status, staff_id
FROM reservation
WHERE status IN ('APPROVED', 'REJECTED', 'COMPLETED')
  AND staff_id IS NULL;
