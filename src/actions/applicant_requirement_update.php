<?php

$applicantId = filter_var(
    $_POST['applicant_id'] ?? '',
    FILTER_VALIDATE_INT
);

$requirementId = filter_var(
    $_POST['requirement_id'] ?? '',
    FILTER_VALIDATE_INT
);

$status = trim($_POST['status'] ?? '');
$submittedDate = trim($_POST['submitted_date'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

$redirectUrl = null;

// Validate IDs.
if (
    $applicantId === false || $applicantId < 1 ||
    $requirementId === false || $requirementId < 1
) {
    throw new HttpException(400, 'Invalid applicant or requirement ID.');
}

$redirectUrl = url('applicant_requirements', [
    'applicant_id' => $applicantId,
]);

// Validate allowed statuses.
$allowedStatuses = [
    'Pending',
    'Submitted',
    'Verified',
    'Rejected',
];

if (!in_array($status, $allowedStatuses, true)) {
    flash_add('error', 'Invalid requirement status.');
    redirect($redirectUrl);
}

// Submitted and Verified require a submission date.
if (
    in_array($status, ['Submitted', 'Verified'], true) &&
    $submittedDate === ''
) {
    flash_add(
        'error',
        'A submission date is required for Submitted or Verified requirements.'
    );

    redirect($redirectUrl);
}

// Validate the date if one was provided.
if ($submittedDate !== '') {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $submittedDate);
    $dateErrors = DateTimeImmutable::getLastErrors();

    if (
        !$date ||
        ($dateErrors !== false &&
            ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
        $date->format('Y-m-d') !== $submittedDate
    ) {
        flash_add('error', 'Please enter a valid submission date.');
        redirect($redirectUrl);
    }
}

// Validate remarks length.
if (strlen($remarks) > 10000) {
    flash_add('error', 'Remarks must not exceed 10,000 characters.');
    redirect($redirectUrl);
}

// Confirm that this requirement is assigned to this applicant.
$assignmentExists = db_query(
    'SELECT 1
       FROM applicant_requirement
      WHERE applicant_id = :applicant_id
        AND requirement_id = :requirement_id',
    [
        ':applicant_id' => $applicantId,
        ':requirement_id' => $requirementId,
    ]
)->fetchColumn();

if (!$assignmentExists) {
    throw new HttpException(404, 'Applicant requirement assignment not found.');
}

// Convert an empty date into NULL.
$submittedDateValue = $submittedDate !== ''
    ? $submittedDate
    : null;

// Save the changes.
db_query(
    'UPDATE applicant_requirement
        SET status = :status,
            submitted_date = :submitted_date,
            remarks = :remarks
      WHERE applicant_id = :applicant_id
        AND requirement_id = :requirement_id',
    [
        ':status' => $status,
        ':submitted_date' => $submittedDateValue,
        ':remarks' => $remarks !== '' ? $remarks : null,
        ':applicant_id' => $applicantId,
        ':requirement_id' => $requirementId,
    ]
);

flash_add('success', 'Applicant requirement updated successfully.');

redirect($redirectUrl);