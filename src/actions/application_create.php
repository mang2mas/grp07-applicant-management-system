<?php
/**
 * POST action: submit a new application (existing applicant -> open position).
 */

$applicantId = filter_var($_POST['applicant_id'] ?? '', FILTER_VALIDATE_INT);
$positionId  = filter_var($_POST['position_id'] ?? '', FILTER_VALIDATE_INT);
$old         = ['applicant_id' => (int) $applicantId, 'position_id' => (int) $positionId];
$errors      = [];

if ($applicantId === false || $applicantId < 1) {
    $errors['applicant_id'] = 'Select an applicant.';
} elseif (!db_query('SELECT 1 FROM applicant WHERE applicant_id = :id', [':id' => $applicantId])->fetchColumn()) {
    $errors['applicant_id'] = 'The selected applicant does not exist.';
}

if ($positionId === false || $positionId < 1) {
    $errors['position_id'] = 'Select a position.';
} else {
    $positionStatus = db_query('SELECT status FROM position WHERE position_id = :id', [':id' => $positionId])->fetchColumn();
    if ($positionStatus === false) {
        $errors['position_id'] = 'The selected position does not exist.';
    } elseif ($positionStatus !== 'Open') {
        $errors['position_id'] = 'This position is not open for applications.';
    }
}

$pendingId = db_query("SELECT status_id FROM application_status WHERE status_name = 'Pending'")->fetchColumn();
if ($pendingId === false) {
    throw new HttpException(500, "The 'Pending' application status is missing. Load db/02_seed.sql.");
}

if ($errors) {
    form_state_set($errors, $old);
    flash_add('error', 'Please fix the highlighted fields.');
    redirect(url('application_form'));
}

try {
    db_query(
        'INSERT INTO application (applicant_id, position_id, status_id, application_date)
         VALUES (:applicant_id, :position_id, :status_id, CURRENT_DATE)',
        [':applicant_id' => $applicantId, ':position_id' => $positionId, ':status_id' => (int) $pendingId]
    );
} catch (PDOException $e) {
    if ($e->getCode() === '23505') { // uq_application_applicant_position
        form_state_set(['position_id' => 'This applicant has already applied for that position.'], $old);
        flash_add('error', 'Please fix the highlighted fields.');
        redirect(url('application_form'));
    }
    throw $e;
}

flash_add('success', 'Application submitted with status Pending.');
redirect(url('applications'));
