<?php
/**
 * POST action: delete an application (its interviews cascade).
 */

$applicationId = filter_var($_POST['application_id'] ?? '', FILTER_VALIDATE_INT);
if ($applicationId === false || $applicationId < 1) {
    throw new HttpException(400, 'Invalid application.');
}

$stmt = db_query('DELETE FROM application WHERE application_id = :id', [':id' => $applicationId]);

if ($stmt->rowCount() === 0) {
    flash_add('error', 'That application no longer exists.');
} else {
    flash_add('success', 'Application deleted.');
}

redirect(return_url('applications', ['status_id', 'position_id', 'p']));
