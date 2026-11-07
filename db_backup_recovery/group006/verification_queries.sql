-- Activity 6: Data Integrity & Recovery Verification Script
-- System: Car Rental Management System (Group 06)
-- Target DB: car_rental_recovery_test

\echo '======================================================='
\echo '  1. RECORD COUNT INTEGRITY CHECK'
\echo '======================================================='
SELECT 'vehicle_category' AS table_name, COUNT(*) AS total_records FROM vehicle_category
UNION ALL
SELECT 'vehicle', COUNT(*) FROM vehicle
UNION ALL
SELECT 'rental_rate', COUNT(*) FROM rental_rate
UNION ALL
SELECT 'customer', COUNT(*) FROM customer
UNION ALL
SELECT 'staff', COUNT(*) FROM staff
UNION ALL
SELECT 'driver_license', COUNT(*) FROM driver_license
UNION ALL
SELECT 'rental', COUNT(*) FROM rental
UNION ALL
SELECT 'cancellation', COUNT(*) FROM cancellation
UNION ALL
SELECT 'vehicle_inspection', COUNT(*) FROM vehicle_inspection
UNION ALL
SELECT 'inspection_item', COUNT(*) FROM inspection_item
UNION ALL
SELECT 'inspection_photo', COUNT(*) FROM inspection_photo
UNION ALL
SELECT 'damage_incident', COUNT(*) FROM damage_incident
UNION ALL
SELECT 'damage_fee', COUNT(*) FROM damage_fee
UNION ALL
SELECT 'payment', COUNT(*) FROM payment
UNION ALL
SELECT 'vehicle_maintenance', COUNT(*) FROM vehicle_maintenance
UNION ALL
SELECT 'audit_log', COUNT(*) FROM audit_log;

\echo '======================================================='
\echo '  2. RENTAL CONFLICT GIST EXCLUSION CONSTRAINT CHECK'
\echo '======================================================='
-- Verify that the exclusion constraint successfully prevents overlapping active bookings for the same vehicle
SELECT 
    r1.rental_id AS booking_1,
    r2.rental_id AS booking_2,
    r1.vehicle_id,
    r1.pickup_datetime AS b1_pickup,
    r1.return_datetime AS b1_return,
    r2.pickup_datetime AS b2_pickup,
    r2.return_datetime AS b2_return
FROM rental r1
JOIN rental r2 ON r1.vehicle_id = r2.vehicle_id AND r1.rental_id < r2.rental_id
WHERE r1.rental_status IN ('Pending', 'Approved', 'Active')
  AND r2.rental_status IN ('Pending', 'Approved', 'Active')
  AND r1.pickup_datetime < r2.return_datetime AND r2.pickup_datetime < r1.return_datetime;

\echo '======================================================='
\echo '  3. PAYMENT LEDGER INTEGRITY CHECK'
\echo '======================================================='
SELECT 
    p.payment_id,
    p.payment_code,
    p.amount,
    p.payment_status,
    p.payment_method,
    COALESCE(r.rental_id::text, 'N/A') AS linked_rental,
    COALESCE(df.damage_fee_id::text, 'N/A') AS linked_damage_fee
FROM payment p
LEFT JOIN rental r ON p.rental_id = r.rental_id
LEFT JOIN damage_fee df ON p.damage_fee_id = df.damage_fee_id;

\echo '======================================================='
\echo '  4. AUDIT LOG INTEGRITY CHECK'
\echo '======================================================='
SELECT audit_id, staff_id, action_type, table_name, action_datetime
FROM audit_log
ORDER BY action_datetime DESC
LIMIT 5;
