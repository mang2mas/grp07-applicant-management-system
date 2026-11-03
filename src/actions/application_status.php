<?php
/**
 * POST action: move an application to another status.
 */

$applicationId = filter_var($_POST['application_id'] ?? '', FILTER_VALIDATE_INT);
$statusId      = filter_var($_POST['status_id'] ?? '', FILTER_VALIDATE_INT);

if ($applicationId === false || $applicationId < 1 || $statusId === false || $statusId < 1) {
    throw new HttpException(400, 'Invalid application or status.');
}

$statusName = db_query('SELECT status_name FROM application_status WHERE status_id = :id', [':id' => $statusId])->fetchColumn();
if ($statusName === false) {
    throw new HttpException(400, 'Unknown status.');
}

$stmt = db_query(
    'UPDATE application SET status_id = :status_id WHERE application_id = :id',
    [':status_id' => $statusId, ':id' => $applicationId]
);

if ($stmt->rowCount() === 0) {
    flash_add('error', 'That application no longer exists.');
} else {
    flash_add('success', 'Application status changed to ' . $statusName . '.');
}

redirect(return_url('applications', ['status_id', 'position_id', 'p']));
