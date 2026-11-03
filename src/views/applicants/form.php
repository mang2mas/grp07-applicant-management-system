<?php
$activeNav = 'applicants';

$id        = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$applicant = null;
if ($id !== false && $id !== null) {
    $applicant = db_query(
        'SELECT applicant_id, first_name, last_name, email, phone, address, date_of_birth
           FROM applicant WHERE applicant_id = :id',
        [':id' => $id]
    )->fetch();
    if (!$applicant) {
        throw new HttpException(404, 'Applicant not found.');
    }
}
$isEdit    = $applicant !== null;
$pageTitle = $isEdit ? 'Edit applicant' : 'Register applicant';

[$errors, $old] = form_state_pull();
$values = $old ?: ($applicant ?: []);
$val    = static fn (string $key): string => (string) ($values[$key] ?? '');

require SRC_PATH . '/views/partials/header.php';
?>
<div class="mx-auto max-w-2xl">
    <a href="<?= e(url('applicants')) ?>" class="text-sm text-indigo-600 hover:underline">&larr; Back to applicants</a>
    <h1 class="mt-2 text-2xl font-semibold"><?= e($pageTitle) ?></h1>

    <form method="post" action="index.php" novalidate class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="applicant_save">
        <?php if ($isEdit): ?>
            <input type="hidden" name="applicant_id" value="<?= e($applicant['applicant_id']) ?>">
        <?php endif; ?>

        <div class="grid gap-5 sm:grid-cols-2">
            <?= form_input('first_name', 'First name', $val('first_name'), $errors, 'text', ['required' => true, 'maxlength' => '50', 'autocomplete' => 'given-name']) ?>
            <?= form_input('last_name', 'Last name', $val('last_name'), $errors, 'text', ['required' => true, 'maxlength' => '50', 'autocomplete' => 'family-name']) ?>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <?= form_input('email', 'Email', $val('email'), $errors, 'email', ['required' => true, 'maxlength' => '100', 'autocomplete' => 'email']) ?>
            <?= form_input('phone', 'Phone', $val('phone'), $errors, 'tel', ['required' => true, 'maxlength' => '20', 'placeholder' => '09171234567', 'autocomplete' => 'tel']) ?>
        </div>

        <?= form_input('date_of_birth', 'Date of birth (optional)', $val('date_of_birth'), $errors, 'date', ['max' => date('Y-m-d')]) ?>

        <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Address (optional)</label>
            <textarea id="address" name="address" rows="3" maxlength="500"
                      class="mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 <?= isset($errors['address']) ? 'border-red-400' : 'border-gray-300' ?>"><?= e($val('address')) ?></textarea>
            <?php if (isset($errors['address'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($errors['address']) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="<?= e(url('applicants')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"><?= $isEdit ? 'Save changes' : 'Register applicant' ?></button>
        </div>
    </form>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
