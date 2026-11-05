<?php

$id = filter_var(
    $_POST['requirement_id'] ?? '',
    FILTER_VALIDATE_INT
);

if ($id === false || $id < 1) {
    throw new HttpException(400, 'Invalid requirement ID.');
}

// Check whether this requirement is assigned to any applicant.
$assignedCount = (int) db_query(
    'SELECT COUNT(*)
     FROM applicant_requirement
     WHERE requirement_id = :id',
    [':id' => $id]
)->fetchColumn();

// Prevent deletion if it is currently in use.
if ($assignedCount > 0) {
    flash_add(
        'error',
        'This requirement is assigned to applicants and cannot be deleted.'
    );

    redirect(return_url('requirements', ['q', 'p']));
}

try {
    $stmt = db_query(
        'DELETE FROM requirement
         WHERE requirement_id = :id',
        [':id' => $id]
    );

} catch (PDOException $e) {

    // Final protection against foreign key violations.
    if ($e->getCode() === '23503') {
        flash_add(
            'error',
            'This requirement is still being used and cannot be deleted.'
        );

        redirect(return_url('requirements', ['q', 'p']));
    }

    throw $e;
}

if ($stmt->rowCount() === 0) {
    throw new HttpException(404, 'Requirement not found.');
}

flash_add('success', 'Requirement deleted successfully.');

redirect(return_url('requirements', ['q', 'p']));