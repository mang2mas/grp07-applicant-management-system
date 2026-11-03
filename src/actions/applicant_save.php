<?php
/**
 * POST action: create or update an applicant (validated server-side).
 */

$id = filter_var($_POST['applicant_id'] ?? '', FILTER_VALIDATE_INT);
$id = ($id !== false && $id > 0) ? $id : null;

$data = [
    'first_name'    => trim((string) ($_POST['first_name'] ?? '')),
    'last_name'     => trim((string) ($_POST['last_name'] ?? '')),
    'email'         => strtolower(trim((string) ($_POST['email'] ?? ''))),
    'phone'         => trim((string) ($_POST['phone'] ?? '')),
    'address'       => trim((string) ($_POST['address'] ?? '')),
    'date_of_birth' => trim((string) ($_POST['date_of_birth'] ?? '')),
];

$errors = [];

foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
    if ($data[$field] === '') {
        $errors[$field] = "$label is required.";
    } elseif (mb_strlen($data[$field]) > 50) {
        $errors[$field] = "$label must be 50 characters or fewer.";
    }
}

if ($data['email'] === '') {
    $errors['email'] = 'Email is required.';
} elseif (mb_strlen($data['email']) > 100 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Enter a valid email address (100 characters maximum).';
}

if ($data['phone'] === '') {
    $errors['phone'] = 'Phone number is required.';
} elseif (!preg_match('/^\+?[0-9][0-9\s().-]{5,18}[0-9]$/', $data['phone'])) {
    $errors['phone'] = 'Enter a valid phone number (digits, spaces, + ( ) - only; 7-20 characters).';
}

if ($data['address'] !== '' && mb_strlen($data['address']) > 500) {
    $errors['address'] = 'Address must be 500 characters or fewer.';
}

if ($data['date_of_birth'] !== '') {
    if (!is_valid_date($data['date_of_birth'])) {
        $errors['date_of_birth'] = 'Enter a valid date.';
    } elseif ($data['date_of_birth'] > date('Y-m-d')) {
        $errors['date_of_birth'] = 'Date of birth cannot be in the future.';
    }
}

$back = url('applicant_form', $id ? ['id' => $id] : []);

if ($errors) {
    form_state_set($errors, $data);
    flash_add('error', 'Please fix the highlighted fields.');
    redirect($back);
}

$params = [
    ':first_name' => $data['first_name'],
    ':last_name'  => $data['last_name'],
    ':email'      => $data['email'],
    ':phone'      => $data['phone'],
    ':address'    => $data['address'] === '' ? null : $data['address'],
    ':dob'        => $data['date_of_birth'] === '' ? null : $data['date_of_birth'],
];

try {
    if ($id) {
        $stmt = db_query(
            'UPDATE applicant
                SET first_name = :first_name, last_name = :last_name, email = :email,
                    phone = :phone, address = :address, date_of_birth = :dob
              WHERE applicant_id = :id',
            $params + [':id' => $id]
        );
        if ($stmt->rowCount() === 0) {
            throw new HttpException(404, 'Applicant not found.');
        }
        flash_add('success', 'Applicant updated.');
    } else {
        db_query(
            'INSERT INTO applicant (first_name, last_name, email, phone, address, date_of_birth)
             VALUES (:first_name, :last_name, :email, :phone, :address, :dob)',
            $params
        );
        flash_add('success', 'Applicant registered.');
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23505') { // unique_violation (applicant.email)
        form_state_set(['email' => 'This email address is already registered.'], $data);
        flash_add('error', 'Please fix the highlighted fields.');
        redirect($back);
    }
    throw $e;
}

redirect(url('applicants'));
