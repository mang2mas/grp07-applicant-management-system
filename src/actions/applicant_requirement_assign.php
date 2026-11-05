<?php

$applicantId = filter_var(
    $_POST['applicant_id'] ?? '',
    FILTER_VALIDATE_INT
);

$requirementId = filter_var(
    $_POST['requirement_id'] ?? '',
    FILTER_VALIDATE_INT
);

// Validate IDs.
if (
    $applicantId === false || $applicantId < 1 ||
    $requirementId === false || $requirementId < 1
) {
    throw new HttpException(400, 'Invalid applicant or requirement ID.');
}

// Make sure the applicant exists.
$applicantExists = db_query(
    'SELECT 1
       FROM applicant
      WHERE applicant_id = :id',
    [':id' => $applicantId]
)->fetchColumn();

if (!$applicantExists) {
    throw new HttpException(404, 'Applicant not found.');
}

// Make sure the requirement exists.
$requirementExists = db_query(
    'SELECT 1
       FROM requirement
      WHERE requirement_id = :id',
    [':id' => $requirementId]
)->fetchColumn();

if (!$requirementExists) {
    throw new HttpException(404, 'Requirement not found.');
}

// Assign the requirement.
// ON CONFLICT prevents duplicate applicant-requirement records.
$stmt = db_query(
    "INSERT INTO applicant_requirement (
        applicant_id,
        requirement_id,
        status
    )
    VALUES (
        :applicant_id,
        :requirement_id,
        'Pending'
    )
    ON CONFLICT (applicant_id, requirement_id) DO NOTHING",
    [
        ':applicant_id' => $applicantId,
        ':requirement_id' => $requirementId,
    ]
);

if ($stmt->rowCount() === 0) {
    flash_add('error', 'This requirement is already assigned to the applicant.');
} else {
    flash_add('success', 'Requirement assigned successfully.');
}

redirect(url('applicant_requirements', [
    'applicant_id' => $applicantId,
]));