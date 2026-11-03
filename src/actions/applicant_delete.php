<?php
/**
 * POST action: delete an applicant.
 * Foreign keys (ON DELETE CASCADE) remove the applicant's applications,
 * interviews, and requirement records automatically.
 */

$id = filter_var($_POST['applicant_id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    throw new HttpException(400, 'Invalid applicant.');
}

$stmt = db_query('DELETE FROM applicant WHERE applicant_id = :id', [':id' => $id]);

if ($stmt->rowCount() === 0) {
    flash_add('error', 'That applicant no longer exists.');
} else {
    flash_add('success', 'Applicant and related records deleted.');
}

redirect(return_url('applicants', ['q', 'status', 'p']));
