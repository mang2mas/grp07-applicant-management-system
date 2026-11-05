<?php
$activeNav = 'requirements';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$requirement = null;

if ($id !== false && $id !== null) {
    $requirement = db_query(
        'SELECT requirement_id, requirement_name, description, is_required
         FROM requirement
         WHERE requirement_id = :id',
        [':id' => $id]
    )->fetch();

    if (!$requirement) {
        throw new HttpException(404, 'Requirement not found.');
    }
}

$isEdit = $requirement !== null;
$pageTitle = $isEdit ? 'Edit requirement' : 'Add requirement';

[$errors, $old] = form_state_pull();
$values = $old ?: ($requirement ?: []);

$val = static fn (string $key): string => (string) ($values[$key] ?? '');

$isRequired = array_key_exists('is_required', $values)
    ? (bool) $values['is_required']
    : (bool) ($requirement['is_required'] ?? true);

require SRC_PATH . '/views/partials/header.php';
?>

<div class="mx-auto max-w-2xl">
    <a href="<?= e(url('requirements')) ?>" class="text-sm text-indigo-600 hover:underline">
        &larr; Back to requirements
    </a>

    <h1 class="mt-2 text-2xl font-semibold"><?= e($pageTitle) ?></h1>

    <p class="mt-1 text-sm text-gray-600">
        Add a document or qualification that applicants may need to submit.
    </p>

    <form method="post" action="index.php"
          class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">

        <?= csrf_field() ?>

        <input type="hidden" name="action" value="requirement_save">

        <?php if ($isEdit): ?>
            <input type="hidden" name="requirement_id"
                   value="<?= e($requirement['requirement_id']) ?>">
        <?php endif; ?>

        <div>
            <label for="requirement_name" class="block text-sm font-medium text-gray-700">
                Requirement name
            </label>

            <input
                id="requirement_name"
                name="requirement_name"
                type="text"
                required
                maxlength="100"
                value="<?= e($val('requirement_name')) ?>"
                placeholder="e.g. Resume, Transcript of Records"
                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['requirement_name']) ? 'border-red-400' : 'border-gray-300' ?>"
            >

            <?php if (isset($errors['requirement_name'])): ?>
                <p class="mt-1 text-sm text-red-600">
                    <?= e($errors['requirement_name']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">
                Description
                <span class="font-normal text-gray-400">(optional)</span>
            </label>

            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="10000"
                placeholder="Describe this requirement..."
                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['description']) ? 'border-red-400' : 'border-gray-300' ?>"
            ><?= e($val('description')) ?></textarea>

            <?php if (isset($errors['description'])): ?>
                <p class="mt-1 text-sm text-red-600">
                    <?= e($errors['description']) ?>
                </p>
            <?php endif; ?>
        </div>

        <div>
            <label class="flex items-start gap-3">
                <input
                    type="checkbox"
                    name="is_required"
                    value="1"
                    <?= $isRequired ? 'checked' : '' ?>
                    class="mt-1 rounded border-gray-300 text-indigo-600"
                >

                <span>
                    <span class="block text-sm font-medium text-gray-700">
                        Required document
                    </span>
                    <span class="block text-sm text-gray-500">
                        Mark this if applicants must submit this requirement.
                    </span>
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="<?= e(url('requirements')) ?>"
               class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300">
                Cancel
            </a>

            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <?= $isEdit ? 'Save changes' : 'Create requirement' ?>
            </button>
        </div>
    </form>
</div>

<?php require SRC_PATH . '/views/partials/footer.php'; ?>