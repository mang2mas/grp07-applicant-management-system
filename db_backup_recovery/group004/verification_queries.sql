-- Activity 6: Data Integrity & Recovery Verification Script
-- System: HealthBridge Clinic Management System (Group 04)
-- Target DB: healthbridge_recovery_test

\echo '======================================================='
\echo '  1. RECORD COUNT INTEGRITY CHECK'
\echo '======================================================='
SELECT 'specialties' AS table_name, COUNT(*) AS total_records FROM specialties
UNION ALL
SELECT 'doctors', COUNT(*) FROM doctors
UNION ALL
SELECT 'patients', COUNT(*) FROM patients
UNION ALL
SELECT 'appointments', COUNT(*) FROM appointments
UNION ALL
SELECT 'medical_notes', COUNT(*) FROM medical_notes
UNION ALL
SELECT 'prescriptions', COUNT(*) FROM prescriptions
UNION ALL
SELECT 'staff_users', COUNT(*) FROM staff_users
UNION ALL
SELECT 'staff_password_resets', COUNT(*) FROM staff_password_resets
UNION ALL
SELECT 'patient_password_resets', COUNT(*) FROM patient_password_resets
UNION ALL
SELECT 'billing', COUNT(*) FROM billing
UNION ALL
SELECT 'deleted_records', COUNT(*) FROM deleted_records;

\echo '======================================================='
\echo '  2. VIEW INTEGRITY CHECK (appointment_details)'
\echo '======================================================='
SELECT * FROM appointment_details LIMIT 5;

\echo '======================================================='
\echo '  3. VIEW INTEGRITY CHECK (visit_records)'
\echo '======================================================='
SELECT * FROM visit_records LIMIT 5;

\echo '======================================================='
\echo '  4. APPOINTMENT SLOT UNIQUE CONSTRAINT CHECK'
\echo '======================================================='
-- Verify partial index prevents duplicate active appointment slots for a doctor
SELECT doctor_id, appointment_date, COUNT(*)
FROM appointments
WHERE status <> 'Cancelled'
GROUP BY doctor_id, appointment_date
HAVING COUNT(*) > 1;

\echo '======================================================='
\echo '  5. CLINICAL & BILLING RELATION INTEGRITY'
\echo '======================================================='
SELECT 
    a.appointment_id,
    p.first_name || ' ' || p.last_name AS patient_name,
    'Dr. ' || d.first_name || ' ' || d.last_name AS doctor_name,
    a.appointment_date,
    a.status AS appointment_status,
    mn.diagnosis,
    b.amount AS billing_amount,
    b.status AS billing_status
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN doctors d ON a.doctor_id = d.doctor_id
LEFT JOIN medical_notes mn ON a.appointment_id = mn.appointment_id
LEFT JOIN billing b ON a.appointment_id = b.appointment_id;
