<?php
$id = filter_var($_POST['position_id'] ?? '', FILTER_VALIDATE_INT);
$id = ($id !== false && $id > 0) ? $id : null;
$data = [
    'position_title' => trim((string) ($_POST['position_title'] ?? '')),
    'department_id' => trim((string) ($_POST['department_id'] ?? '')),
    'description' => trim((string) ($_POST['description'] ?? '')),
    'employment_type' => trim((string) ($_POST['employment_type'] ?? 'Full-time')),
    'status' => trim((string) ($_POST['status'] ?? 'Open')),
];
$errors = [];
if ($data['position_title'] === '') {
    $errors['position_title'] = 'Position title is required.';
} elseif (mb_strlen($data['position_title']) > 100) {
    $errors['position_title'] = 'Position title must be 100 characters or fewer.';
}
$departmentId = filter_var($data['department_id'], FILTER_VALIDATE_INT);
if ($departmentId === false || $departmentId < 1 ||
    !db_query('SELECT 1 FROM department WHERE department_id = :id', [':id' => (int) $departmentId])->fetchColumn()) {
    $errors['department_id'] = 'Select a valid department.';
}
if (mb_strlen($data['description']) > 10000) {
    $errors['description'] = 'Description is too long (10,000 characters maximum).';
}
$employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Temporary'];
$statuses = ['Open', 'Closed', 'On Hold'];
if (!in_array($data['employment_type'], $employmentTypes, true)) {
    $errors['employment_type'] = 'Select a valid employment type.';
}
if (!in_array($data['status'], $statuses, true)) {
    $errors['status'] = 'Select a valid position status.';
}
$back = url('position_form', $id ? ['id' => $id] : []);
if ($errors) {
    form_state_set($errors, $data);
    flash_add('error', 'Please fix the highlighted fields.');
    redirect($back);
}
$params = [
    ':title' => $data['position_title'],
    ':department' => (int) $departmentId,
    ':description' => $data['description'] === '' ? null : $data['description'],
    ':type' => $data['employment_type'],
    ':status' => $data['status'],
];
if ($id) {
    $stmt = db_query(
        'UPDATE position SET position_title = :title, department_id = :department,
                description = :description, employment_type = :type, status = :status
          WHERE position_id = :id',
        $params + [':id' => $id]
    );
    if ($stmt->rowCount() === 0 && !db_query('SELECT 1 FROM position WHERE position_id = :id', [':id' => $id])->fetchColumn()) {
        throw new HttpException(404, 'Position not found.');
    }
    flash_add('success', 'Position updated successfully.');
} else {
    db_query(
        'INSERT INTO position (department_id, position_title, description, employment_type, status)
         VALUES (:department, :title, :description, :type, :status)',
        $params
    );
    flash_add('success', 'Position created successfully.');
}
redirect(url('positions'));
