<?php
$id = filter_var($_POST['department_id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    throw new HttpException(400, 'Invalid department ID.');
}

$positionCount = (int) db_query(
    'SELECT COUNT(*) FROM position WHERE department_id = :id',
    [':id' => $id]
)->fetchColumn();

if ($positionCount > 0) {
    flash_add('error', 'This department has positions assigned to it. Move or delete those positions before deleting the department.');
    redirect(return_url('departments', ['q', 'p']));
}

try {
    $stmt = db_query('DELETE FROM department WHERE department_id = :id', [':id' => $id]);
} catch (PDOException $e) {
    // Keep the database FK as the final protection if a related position is added concurrently.
    if ($e->getCode() === '23503') {
        flash_add('error', 'This department is still being used by a position and cannot be deleted.');
        redirect(return_url('departments', ['q', 'p']));
    }
    throw $e;
}

if ($stmt->rowCount() === 0) {
    throw new HttpException(404, 'Department not found.');
}

flash_add('success', 'Department deleted successfully.');
redirect(return_url('departments', ['q', 'p']));
