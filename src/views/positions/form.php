<?php
$activeNav = 'positions';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$position = null;
if ($id !== false && $id !== null) {
    $position = db_query(
        'SELECT position_id, department_id, position_title, description, employment_type, status
           FROM position WHERE position_id = :id',
        [':id' => $id]
    )->fetch();
    if (!$position) {
        throw new HttpException(404, 'Position not found.');
    }
}
$isEdit = $position !== null;
$pageTitle = $isEdit ? 'Edit position' : 'Add position';
$departments = db_query('SELECT department_id, department_name FROM department ORDER BY department_name')->fetchAll();
[$errors, $old] = form_state_pull();
$values = $old ?: ($position ?: []);
$val = static fn (string $key): string => (string) ($values[$key] ?? '');
$employmentTypes = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Temporary'];
$statuses = ['Open', 'Closed', 'On Hold'];

require SRC_PATH . '/views/partials/header.php';
?>
<div class="mx-auto max-w-2xl">
    <a href="<?= e(url('positions')) ?>" class="text-sm text-indigo-600 hover:underline">&larr; Back to positions</a>
    <h1 class="mt-2 text-2xl font-semibold"><?= e($pageTitle) ?></h1>
    <p class="mt-1 text-sm text-gray-600">Provide the position details and assign it to a department.</p>

    <?php if (!$departments): ?>
        <div class="mt-6 rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">No departments exist yet. Add a department before creating a position.</div>
    <?php else: ?>
    <form method="post" action="index.php" novalidate class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="position_save">
        <?php if ($isEdit): ?><input type="hidden" name="position_id" value="<?= e($position['position_id']) ?>"><?php endif; ?>

        <div>
            <label for="position_title" class="block text-sm font-medium text-gray-700">Position title</label>
            <input id="position_title" name="position_title" required maxlength="100" value="<?= e($val('position_title')) ?>" placeholder="e.g. Software Developer"
                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['position_title']) ? 'border-red-400' : 'border-gray-300' ?>">
            <?php if (isset($errors['position_title'])): ?><p class="mt-1 text-sm text-red-600"><?= e($errors['position_title']) ?></p><?php endif; ?>
        </div>

        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
            <select id="department_id" name="department_id" required class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['department_id']) ? 'border-red-400' : 'border-gray-300' ?>">
                <option value="">Select department…</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= e($d['department_id']) ?>" <?= (int) $d['department_id'] === (int) $val('department_id') ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['department_id'])): ?><p class="mt-1 text-sm text-red-600"><?= e($errors['department_id']) ?></p><?php endif; ?>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="employment_type" class="block text-sm font-medium text-gray-700">Employment type</label>
                <select id="employment_type" name="employment_type" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <?php foreach ($employmentTypes as $type): ?><option value="<?= e($type) ?>" <?= $val('employment_type') === $type || (!$val('employment_type') && $type === 'Full-time') ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $val('status') === $status || (!$val('status') && $status === 'Open') ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['description']) ? 'border-red-400' : 'border-gray-300' ?>"><?= e($val('description')) ?></textarea>
            <?php if (isset($errors['description'])): ?><p class="mt-1 text-sm text-red-600"><?= e($errors['description']) ?></p><?php endif; ?>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="<?= e(url('positions')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"><?= $isEdit ? 'Save changes' : 'Create position' ?></button>
        </div>
    </form>
    <?php endif; ?>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
