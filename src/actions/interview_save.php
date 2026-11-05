<?php

$id = filter_var($_POST['interview_id'] ?? '', FILTER_VALIDATE_INT);
$id = ($id !== false && $id > 0) ? $id : null;

$data = [
    'application_id' => trim((string) ($_POST['application_id'] ?? '')),
    'interview_date' => trim((string) ($_POST['interview_date'] ?? '')),
    'interview_type' => trim((string) ($_POST['interview_type'] ?? 'In-person')),
    'result' => trim((string) ($_POST['result'] ?? 'Pending')),
    'notes' => trim((string) ($_POST['notes'] ?? '')),
];

$errors = [];

// Validate application.
$applicationId = filter_var(
    $data['application_id'],
    FILTER_VALIDATE_INT
);

if (
    $applicationId === false ||
    $applicationId < 1 ||
    !db_query(
        'SELECT 1 FROM application WHERE application_id = :id',
        [':id' => (int) $applicationId]
    )->fetchColumn()
) {
    $errors['application_id'] = 'Select a valid applicant application.';
}

// Validate interview date and time.
$interviewDate = null;

$dateObject = DateTimeImmutable::createFromFormat(
    '!Y-m-d\TH:i',
    $data['interview_date']
);

$dateErrors = DateTimeImmutable::getLastErrors();

if (
    !$dateObject ||
    ($dateErrors !== false &&
        ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
    $dateObject->format('Y-m-d\TH:i') !== $data['interview_date']
) {
    $errors['interview_date'] = 'Enter a valid interview date and time.';
} else {
    $interviewDate = $dateObject->format('Y-m-d H:i:s');
}

// Validate interview type.
$allowedTypes = [
    'Phone',
    'Video',
    'In-person',
    'Panel',
    'Technical',
];

if (!in_array($data['interview_type'], $allowedTypes, true)) {
    $errors['interview_type'] = 'Select a valid interview type.';
}

// Validate result.
$allowedResults = [
    'Pending',
    'Passed',
    'Failed',
    'No Show',
    'Rescheduled',
];

if (!in_array($data['result'], $allowedResults, true)) {
    $errors['result'] = 'Select a valid interview result.';
}

// Validate notes.
if (mb_strlen($data['notes']) > 10000) {
    $errors['notes'] = 'Interview notes must not exceed 10,000 characters.';
}

// Keep the user on the correct form if validation fails.
$backUrl = url(
    'interview_form',
    $id ? ['id' => $id] : []
);

if ($errors) {
    form_state_set($errors, $data);
    flash_add('error', 'Please fix the highlighted fields.');
    redirect($backUrl);
}

// Save interview.
$params = [
    ':application_id' => (int) $applicationId,
    ':interview_date' => $interviewDate,
    ':interview_type' => $data['interview_type'],
    ':result' => $data['result'],
    ':notes' => $data['notes'] !== '' ? $data['notes'] : null,
];

if ($id) {

    $stmt = db_query(
        'UPDATE interview
            SET application_id = :application_id,
                interview_date = :interview_date,
                interview_type = :interview_type,
                result = :result,
                notes = :notes
          WHERE interview_id = :id',
        $params + [':id' => $id]
    );

    if (
        $stmt->rowCount() === 0 &&
        !db_query(
            'SELECT 1 FROM interview WHERE interview_id = :id',
            [':id' => $id]
        )->fetchColumn()
    ) {
        throw new HttpException(404, 'Interview not found.');
    }

    flash_add('success', 'Interview updated successfully.');

} else {

    db_query(
        'INSERT INTO interview (
            application_id,
            interview_date,
            interview_type,
            result,
            notes
        )
        VALUES (
            :application_id,
            :interview_date,
            :interview_type,
            :result,
            :notes
        )',
        $params
    );

    flash_add('success', 'Interview scheduled successfully.');
}

redirect(url('interviews'));