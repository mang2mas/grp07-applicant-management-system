-- Activity 6: Data Integrity & Recovery Verification Script
-- System: Catering Business Management System (Group 08)
-- Target DB: catering_recovery_test

\echo '======================================================='
\echo '  1. RECORD COUNT INTEGRITY CHECK'
\echo '======================================================='
SELECT 'customer' AS table_name, COUNT(*) AS total_records FROM customer
UNION ALL
SELECT 'package', COUNT(*) FROM package
UNION ALL
SELECT 'menu_item', COUNT(*) FROM menu_item
UNION ALL
SELECT 'employee', COUNT(*) FROM employee
UNION ALL
SELECT 'inventory', COUNT(*) FROM inventory
UNION ALL
SELECT 'reservation', COUNT(*) FROM reservation
UNION ALL
SELECT 'payment', COUNT(*) FROM payment
UNION ALL
SELECT 'reservation_menu', COUNT(*) FROM reservation_menu
UNION ALL
SELECT 'event_assignment', COUNT(*) FROM event_assignment
UNION ALL
SELECT 'cancellation', COUNT(*) FROM cancellation;

\echo '======================================================='
\echo '  2. RESERVATION & CUSTOMER RELATION INTEGRITY'
\echo '======================================================='
SELECT 
    r.reservation_id,
    c.first_name || ' ' || c.last_name AS customer_name,
    p.package_name,
    r.event_date,
    r.venue,
    r.event_type,
    r.number_of_guests,
    r.status,
    r.estimated_cost
FROM reservation r
JOIN customer c ON r.customer_id = c.customer_id
JOIN package p ON r.package_id = p.package_id;

\echo '======================================================='
\echo '  3. PAYMENT LEDGER STATUS CHECK'
\echo '======================================================='
SELECT 
    pay.payment_id,
    pay.reservation_id,
    pay.payment_amount,
    pay.payment_date,
    pay.payment_status,
    pay.payment_method
FROM payment pay;

\echo '======================================================='
\echo '  4. INVENTORY STOCK AVAILABILITY CHECK'
\echo '======================================================='
SELECT inventory_id, item_name, unit, quantity, availability_status
FROM inventory
ORDER BY inventory_id;
