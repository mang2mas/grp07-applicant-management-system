<?php
$pageTitle = 'New application';
$activeNav = 'applications';

$applicants = db_query('SELECT applicant_id, first_name, last_name, email FROM applicant ORDER BY last_name, first_name')->fetchAll();
$positions  = db_query(
    "SELECT p.position_id, p.position_title, p.employment_type, d.department_name
       FROM position p JOIN department d ON d.department_id = p.department_id
      WHERE p.status = 'Open'
      ORDER BY d.department_name, p.position_title"
)->fetchAll();

[$errors, $old] = form_state_pull();

// Preselect from the query string (e.g. a link from another page) unless re-showing a failed submit.
$selApplicant = (int) ($old['applicant_id'] ?? (filter_var($_GET['applicant_id'] ?? '', FILTER_VALIDATE_INT) ?: 0));
$selPosition  = (int) ($old['position_id'] ?? (filter_var($_GET['position_id'] ?? '', FILTER_VALIDATE_INT) ?: 0));

$selectClass = static fn (string $field): string =>
    'mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 '
    . (isset($errors[$field]) ? 'border-red-400' : 'border-gray-300');

require SRC_PATH . '/views/partials/header.php';
?>
<div class="mx-auto max-w-2xl">
    <a href="<?= e(url('applications')) ?>" class="text-sm text-indigo-600 hover:underline">&larr; Back to applications</a>
    <h1 class="mt-2 text-2xl font-semibold">New application</h1>
    <p class="mt-1 text-sm text-gray-600">Link an existing applicant to an open position. The application starts as <strong>Pending</strong>.</p>

    <?php if (!$applicants || !$positions): ?>
        <div class="mt-6 rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            <?php if (!$applicants): ?>
                There are no applicants yet. <a href="<?= e(url('applicant_form')) ?>" class="font-medium underline">Register an applicant</a> first.
            <?php else: ?>
                There are no open positions to apply for right now.
            <?php endif; ?>
        </div>
    <?php else: ?>
        <form method="post" action="index.php" novalidate class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="application_create">

            <div>
                <label for="applicant_id" class="block text-sm font-medium text-gray-700">Applicant</label>
                <select id="applicant_id" name="applicant_id" required class="<?= e($selectClass('applicant_id')) ?>">
                    <option value="">Select an applicant…</option>
                    <?php foreach ($applicants as $a): ?>
                        <option value="<?= e($a['applicant_id']) ?>" <?= (int) $a['applicant_id'] === $selApplicant ? 'selected' : '' ?>>
                            <?= e($a['last_name'] . ', ' . $a['first_name'] . ' (' . $a['email'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['applicant_id'])): ?><p class="mt-1 text-sm text-red-600"><?= e($errors['applicant_id']) ?></p><?php endif; ?>
            </div>

            <div>
                <label for="position_id" class="block text-sm font-medium text-gray-700">Open position</label>
                <select id="position_id" name="position_id" required class="<?= e($selectClass('position_id')) ?>">
                    <option value="">Select a position…</option>
                    <?php foreach ($positions as $p): ?>
                        <option value="<?= e($p['position_id']) ?>" <?= (int) $p['position_id'] === $selPosition ? 'selected' : '' ?>>
                            <?= e($p['position_title'] . ' — ' . $p['department_name'] . ' (' . $p['employment_type'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['position_id'])): ?><p class="mt-1 text-sm text-red-600"><?= e($errors['position_id']) ?></p><?php endif; ?>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="<?= e(url('applications')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Submit application</button>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
