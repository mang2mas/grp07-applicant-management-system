-- Activity 6: Data Integrity & Recovery Verification Script
-- System: Pawpaw Clinic Management System (Group 03)
-- Target DB: pawpaw_clinic_recovery_test

\echo '======================================================='
\echo '  1. RECORD COUNT INTEGRITY CHECK'
\echo '======================================================='
SELECT 'USER_ACCOUNT' AS table_name, COUNT(*) AS total_records FROM USER_ACCOUNT
UNION ALL
SELECT 'VETERINARIAN', COUNT(*) FROM VETERINARIAN
UNION ALL
SELECT 'OWNER', COUNT(*) FROM OWNER
UNION ALL
SELECT 'PET', COUNT(*) FROM PET
UNION ALL
SELECT 'SERVICE', COUNT(*) FROM SERVICE
UNION ALL
SELECT 'APPOINTMENT', COUNT(*) FROM APPOINTMENT
UNION ALL
SELECT 'APPOINTMENT_SERVICE', COUNT(*) FROM APPOINTMENT_SERVICE
UNION ALL
SELECT 'MEDICAL_RECORD', COUNT(*) FROM MEDICAL_RECORD
UNION ALL
SELECT 'PRESCRIPTION', COUNT(*) FROM PRESCRIPTION
UNION ALL
SELECT 'VACCINATION', COUNT(*) FROM VACCINATION
UNION ALL
SELECT 'AUDIT_LOG', COUNT(*) FROM AUDIT_LOG
UNION ALL
SELECT 'NOTIFICATION', COUNT(*) FROM NOTIFICATION;

\echo '======================================================='
\echo '  2. TRIGGER FUNCTIONALITY CHECK (Price Snapshotting)'
\echo '======================================================='
-- Verify unit_price locked correctly upon insert via trg_appointment_service_price
SELECT 
    aps.appointment_id,
    s.service_name,
    s.price AS catalog_current_price,
    aps.unit_price AS appointment_locked_price,
    CASE 
        WHEN aps.unit_price IS NOT NULL THEN 'PASSED' 
        ELSE 'FAILED' 
    END AS trigger_verification
FROM APPOINTMENT_SERVICE aps
JOIN SERVICE s ON aps.service_id = s.service_id;

\echo '======================================================='
\echo '  3. FOREIGN KEY RELATION & MEDICAL RECORD INTEGRITY'
\echo '======================================================='
-- Confirm completed appointments correctly map to medical records and pet details
SELECT 
    a.appointment_id,
    p.name AS pet_name,
    v.name AS veterinarian_name,
    a.status AS appointment_status,
    mr.record_id,
    mr.diagnosis
FROM APPOINTMENT a
JOIN PET p ON a.pet_id = p.pet_id
LEFT JOIN VETERINARIAN v ON a.veterinarian_id = v.veterinarian_id
LEFT JOIN MEDICAL_RECORD mr ON a.appointment_id = mr.appointment_id;

\echo '======================================================='
\echo '  4. VACCINATION DATES CONSTRAINT VERIFICATION'
\echo '======================================================='
-- Confirm check constraint: next_due_date >= date_administered
SELECT 
    v.vaccination_id,
    p.name AS pet_name,
    v.vaccine_type,
    v.date_administered,
    v.next_due_date,
    CASE 
        WHEN v.next_due_date IS NULL OR v.next_due_date >= v.date_administered THEN 'VALID' 
        ELSE 'INVALID' 
    END AS date_constraint_status
FROM VACCINATION v
JOIN PET p ON v.pet_id = p.pet_id;
