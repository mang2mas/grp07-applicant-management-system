
<?php
$pageTitle = 'Requirements';
$activeNav = 'requirements';

$q = trim((string) ($_GET['q'] ?? ''));

$where = '';
$params = [];

if ($q !== '') {
    $where = 'WHERE r.requirement_name ILIKE :q1
              OR r.description ILIKE :q2';

    $like = '%' . like_escape($q) . '%';

    $params = [
        ':q1' => $like,
        ':q2' => $like
    ];
}

// Count total requirements
$total = (int) db_query(
    "SELECT COUNT(*)
       FROM requirement r
       $where",
    $params
)->fetchColumn();

// Pagination
$pager = paginate(
    $total,
    (int) ($_GET['p'] ?? 1),
    10
);

// Get requirements and their applicant usage count
$requirements = db_query(
    "SELECT
        r.requirement_id,
        r.requirement_name,
        r.description,
        r.is_required,
        COUNT(ar.applicant_id) AS applicant_count
     FROM requirement r
     LEFT JOIN applicant_requirement ar
        ON ar.requirement_id = r.requirement_id
     $where
     GROUP BY
        r.requirement_id,
        r.requirement_name,
        r.description,
        r.is_required
     ORDER BY r.requirement_name
     LIMIT :limit OFFSET :offset",
    $params + [
        ':limit' => $pager['per_page'],
        ':offset' => $pager['offset']
    ]
)->fetchAll();

$returnQs = http_build_query([
    'q' => $q,
    'p' => $pager['page']
]);

require SRC_PATH . '/views/partials/header.php';
?>

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Requirements</h1>
        <p class="mt-1 text-sm text-gray-600">
            Manage the documents applicants need to submit.
        </p>
    </div>

    <?php if (!is_viewer()): ?><a href="<?= e(url('requirement_form')) ?>"
       class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        + Add requirement
    </a><?php endif; ?>
</div>

<form method="get" action="index.php"
      class="mt-6 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-end">

    <input type="hidden" name="page" value="requirements">

    <div class="flex-1">
        <label for="q" class="block text-sm font-medium text-gray-700">
            Search requirements
        </label>

        <input
            id="q"
            name="q"
            type="search"
            value="<?= e($q) ?>"
            placeholder="Requirement name or description"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
        >
    </div>

    <div class="flex gap-2">
        <button type="submit"
                class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white">
            Search
        </button>

        <?php if ($q !== ''): ?>
            <a href="<?= e(url('requirements')) ?>"
               class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300">
                Reset
            </a>
        <?php endif; ?>
    </div>
</form>

<div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">

            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Requirement</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Applicants Assigned</th>
                    <th class="px-4 py-3 text-right"><?= is_viewer() ? '' : 'Actions' ?></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                <?php foreach ($requirements as $requirement): ?>
                    <tr>
                        <td class="px-4 py-3 font-medium">
                            <?= e($requirement['requirement_name']) ?>
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            <?= $requirement['description']
                                ? nl2br(e($requirement['description']))
                                : '<span class="text-gray-400">No description</span>' ?>
                        </td>

                        <td class="px-4 py-3">
                            <?php if ($requirement['is_required']): ?>
                                <?= badge('Required', 'blue') ?>
                            <?php else: ?>
                                <?= badge('Optional', 'gray') ?>
                            <?php endif; ?>
                        </td>

                        <td class="px-4 py-3">
                            <?= e($requirement['applicant_count']) ?>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <?php if (!is_viewer()): ?><a
                                href="<?= e(url('requirement_form', [
                                    'id' => $requirement['requirement_id']
                                ])) ?>"
                                class="text-indigo-600 hover:underline">
                                Edit
                            </a>

                            <?php if ((int) $requirement['applicant_count'] === 0): ?>
                                <span class="mx-1 text-gray-300">|</span>

                                <form method="post"
                                      action="index.php"
                                      class="inline"
                                      onsubmit="<?= js_confirm('Delete this requirement?') ?>">

                                    <?= csrf_field() ?>

                                    <input type="hidden" name="action"
                                           value="requirement_delete">

                                    <input type="hidden" name="requirement_id"
                                           value="<?= e($requirement['requirement_id']) ?>">

                                    <input type="hidden" name="return_qs"
                                           value="<?= e($returnQs) ?>">

                                    <button type="submit"
                                            class="text-red-600 hover:underline">
                                        Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="ml-2 text-xs text-gray-400">
                                    In use
                                </span>
                            <?php endif; ?><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$requirements): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            No requirements found.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>

    <?= render_pagination('requirements', ['q' => $q], $pager) ?>
</div>

<?php require SRC_PATH . '/views/partials/footer.php'; ?>
