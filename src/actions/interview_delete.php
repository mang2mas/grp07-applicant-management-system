<?php

$id = filter_var(
    $_POST['interview_id'] ?? '',
    FILTER_VALIDATE_INT
);

if ($id === false || $id < 1) {
    throw new HttpException(400, 'Invalid interview ID.');
}

$stmt = db_query(
    'DELETE FROM interview
      WHERE interview_id = :id',
    [':id' => $id]
);

if ($stmt->rowCount() === 0) {
    flash_add('error', 'Interview not found or already deleted.');
} else {
    flash_add('success', 'Interview deleted successfully.');
}

redirect(url('interviews'));