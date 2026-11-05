<?php
$activeNav = 'departments';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$department = null;
if ($id !== false && $id !== null) {
    $department = db_query(
        'SELECT department_id, department_name, description FROM department WHERE department_id = :id',
        [':id' => $id]
    )->fetch();
    if (!$department) {
        throw new HttpException(404, 'Department not found.');
    }
}
$isEdit = $department !== null;
$pageTitle = $isEdit ? 'Edit department' : 'Add department';
[$errors, $old] = form_state_pull();
$values = $old ?: ($department ?: []);
$val = static fn (string $key): string => (string) ($values[$key] ?? '');

require SRC_PATH . '/views/partials/header.php';
?>
<div class="mx-auto max-w-2xl">
    <a href="<?= e(url('departments')) ?>" class="text-sm text-indigo-600 hover:underline">&larr; Back to departments</a>
    <h1 class="mt-2 text-2xl font-semibold"><?= e($pageTitle) ?></h1>
    <p class="mt-1 text-sm text-gray-600">Enter the department details below. Departments are used to organize job positions.</p>

    <form method="post" action="index.php" class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="department_save">
        <?php if ($isEdit): ?>
            <input type="hidden" name="department_id" value="<?= e($department['department_id']) ?>">
        <?php endif; ?>

        <div>
            <label for="department_name" class="block text-sm font-medium text-gray-700">Department name</label>
            <input id="department_name" name="department_name" required maxlength="100" value="<?= e($val('department_name')) ?>" placeholder="e.g. Information Technology"
                   class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['department_name']) ? 'border-red-400' : 'border-gray-300' ?>">
            <?php if (isset($errors['department_name'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($errors['department_name']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description <span class="font-normal text-gray-400">(optional)</span></label>
            <textarea id="description" name="description" rows="4" maxlength="10000" placeholder="Briefly describe this department..."
                      class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['description']) ? 'border-red-400' : 'border-gray-300' ?>"><?= e($val('description')) ?></textarea>
            <?php if (isset($errors['description'])): ?>
                <p class="mt-1 text-sm text-red-600"><?= e($errors['description']) ?></p>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="<?= e(url('departments')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"><?= $isEdit ? 'Save changes' : 'Create department' ?></button>
        </div>
    </form>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
