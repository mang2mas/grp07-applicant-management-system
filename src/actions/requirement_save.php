<?php

$requirementId = filter_var(
    $_POST['requirement_id'] ?? null,
    FILTER_VALIDATE_INT
);

$isEdit = $requirementId !== false
    && $requirementId !== null
    && $requirementId > 0;

if (isset($_POST['requirement_id']) && !$isEdit) {
    flash_add('error', 'Invalid requirement ID.');
    redirect(url('requirements'));
}

$name = trim($_POST['requirement_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$isRequired = isset($_POST['is_required'])
    && $_POST['is_required'] === '1';

$old = [
    'requirement_name' => $name,
    'description' => $description,
    'is_required' => $isRequired,
];

$errors = [];

// Validate requirement name.
if ($name === '') {
    $errors['requirement_name'] = 'Requirement name is required.';
} elseif (
    (function_exists('mb_strlen') ? mb_strlen($name) : strlen($name)) > 100
) {
    $errors['requirement_name'] = 'Requirement name cannot exceed 100 characters.';
}

// Validate description.
if (strlen($description) > 10000) {
    $errors['description'] = 'Description is too long.';
}

// Check for duplicate names.
if (!isset($errors['requirement_name'])) {
    $duplicate = db_query(
        'SELECT requirement_id
         FROM requirement
         WHERE LOWER(requirement_name) = LOWER(:name)
         AND requirement_id <> :id',
        [
            ':name' => $name,
            ':id' => $isEdit ? $requirementId : 0,
        ]
    )->fetch();

    if ($duplicate) {
        $errors['requirement_name'] = 'This requirement already exists.';
    }
}

// Return to the form if validation fails.
if ($errors) {
    form_state_set($errors, $old);

    $destination = $isEdit
        ? url('requirement_form', ['id' => $requirementId])
        : url('requirement_form');

    redirect($destination);
}

try {
    if ($isEdit) {
        // Update existing requirement.
        db_query(
            'UPDATE requirement
             SET requirement_name = :name,
                 description = :description,
                 is_required = :is_required
             WHERE requirement_id = :id',
            [
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':is_required' => $isRequired,
                ':id' => $requirementId,
            ]
        );

        flash_add('success', 'Requirement updated successfully.');
    } else {
        // Create new requirement.
        db_query(
            'INSERT INTO requirement
                (requirement_name, description, is_required)
             VALUES
                (:name, :description, :is_required)',
            [
                ':name' => $name,
                ':description' => $description !== '' ? $description : null,
                ':is_required' => $isRequired,
            ]
        );

        flash_add('success', 'Requirement created successfully.');
    }

    redirect(url('requirements'));

} catch (PDOException $e) {
    // PostgreSQL unique constraint violation.
    if ($e->getCode() === '23505') {
        form_state_set(
            ['requirement_name' => 'This requirement already exists.'],
            $old
        );

        $destination = $isEdit
            ? url('requirement_form', ['id' => $requirementId])
            : url('requirement_form');

        redirect($destination);
    }

    throw $e;
}