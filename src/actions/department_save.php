<?php
$id = filter_var($_POST['department_id'] ?? '', FILTER_VALIDATE_INT);
$id = ($id !== false && $id > 0) ? $id : null;

$data = [
    'department_name' => trim((string) ($_POST['department_name'] ?? '')),
    'description' => trim((string) ($_POST['description'] ?? '')),
];
$errors = [];

if ($data['department_name'] === '') {
    $errors['department_name'] = 'Department name is required.';
} elseif (mb_strlen($data['department_name']) > 100) {
    $errors['department_name'] = 'Department name must be 100 characters or fewer.';
}
if (mb_strlen($data['description']) > 10000) {
    $errors['description'] = 'Description is too long (10,000 characters maximum).';
}

$back = url('department_form', $id ? ['id' => $id] : []);
if ($errors) {
    form_state_set($errors, $data);
    flash_add('error', 'Please fix the highlighted fields.');
    redirect($back);
}

try {
    if ($id) {
        $exists = db_query('SELECT 1 FROM department WHERE department_id = :id', [':id' => $id])->fetchColumn();
        if (!$exists) {
            throw new HttpException(404, 'Department not found.');
        }
        db_query(
            'UPDATE department
                SET department_name = :name, description = :description
              WHERE department_id = :id',
            [
                ':name' => $data['department_name'],
                ':description' => $data['description'] === '' ? null : $data['description'],
                ':id' => $id,
            ]
        );
        flash_add('success', 'Department updated successfully.');
    } else {
        db_query(
            'INSERT INTO department (department_name, description)
             VALUES (:name, :description)',
            [
                ':name' => $data['department_name'],
                ':description' => $data['description'] === '' ? null : $data['description'],
            ]
        );
        flash_add('success', 'Department created successfully.');
    }
} catch (PDOException $e) {
    // PostgreSQL unique_violation: department_name must be unique.
    if ($e->getCode() === '23505') {
        form_state_set(['department_name' => 'A department with this name already exists.'], $data);
        flash_add('error', 'Please use a different department name.');
        redirect($back);
    }
    throw $e;
}

redirect(url('departments'));
