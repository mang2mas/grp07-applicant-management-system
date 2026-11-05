<?php

$applicantId = filter_var(
    $_POST['applicant_id'] ?? '',
    FILTER_VALIDATE_INT
);

$requirementId = filter_var(
    $_POST['requirement_id'] ?? '',
    FILTER_VALIDATE_INT
);

if (
    $applicantId === false || $applicantId < 1 ||
    $requirementId === false || $requirementId < 1
) {
    throw new HttpException(400, 'Invalid applicant or requirement ID.');
}

// Remove only the assignment, not the master requirement.
$stmt = db_query(
    'DELETE FROM applicant_requirement
      WHERE applicant_id = :applicant_id
        AND requirement_id = :requirement_id',
    [
        ':applicant_id' => $applicantId,
        ':requirement_id' => $requirementId,
    ]
);

if ($stmt->rowCount() === 0) {
    flash_add('error', 'Applicant requirement assignment not found.');
} else {
    flash_add('success', 'Requirement assignment removed successfully.');
}

redirect(url('applicant_requirements', [
    'applicant_id' => $applicantId,
]));