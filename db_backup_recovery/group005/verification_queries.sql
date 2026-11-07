-- Activity 6: Data Integrity & Recovery Verification Script
-- System: Shiori Cafe Management System (Group 05)
-- Target DB: shioricafe_recovery_test

\echo '======================================================='
\echo '  1. TABLE & CATALOG RECORD COUNT VERIFICATION'
\echo '======================================================='
SELECT 'shioricafe_schema.table' AS relation_name, COUNT(*) AS total_records FROM shioricafe_schema."table"
UNION ALL
SELECT 'shioricafe_schema.category', COUNT(*) FROM shioricafe_schema.category
UNION ALL
SELECT 'shioricafe_schema.menu_item', COUNT(*) FROM shioricafe_schema.menu_item;

\echo '======================================================='
\echo '  2. SEATING INVENTORY STATUS CHECK'
\echo '======================================================='
SELECT table_id, table_number, table_type, capacity, operational_status
FROM shioricafe_schema."table"
ORDER BY table_id;

\echo '======================================================='
\echo '  3. MENU CATEGORY & ITEM RELATION INTEGRITY'
\echo '======================================================='
SELECT 
    c.category_name,
    c.is_drink_category,
    mi.item_name,
    mi.current_price,
    mi.stock_status,
    mi.is_active
FROM shioricafe_schema.category c
JOIN shioricafe_schema.menu_item mi ON c.category_id = mi.category_id
ORDER BY c.category_name, mi.item_name;
