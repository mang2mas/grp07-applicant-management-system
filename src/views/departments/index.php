<?php
$pageTitle = 'Departments';
$activeNav = 'departments';

$q = trim((string) ($_GET['q'] ?? ''));
$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE d.department_name ILIKE :q1 OR d.description ILIKE :q2';
    $like = '%' . like_escape($q) . '%';
    $params = [':q1' => $like, ':q2' => $like];
}

$total = (int) db_query("SELECT COUNT(*) FROM department d $where", $params)->fetchColumn();
$pager = paginate($total, (int) ($_GET['p'] ?? 1), 10);
$departments = db_query(
    "SELECT d.department_id, d.department_name, d.description,
            COUNT(p.position_id) AS position_count
       FROM department d
       LEFT JOIN position p ON p.department_id = d.department_id
       $where
      GROUP BY d.department_id, d.department_name, d.description
      ORDER BY d.department_name
      LIMIT :limit OFFSET :offset",
    $params + [':limit' => $pager['per_page'], ':offset' => $pager['offset']]
)->fetchAll();

$counts = db_query(
    'SELECT COUNT(*) AS total,
            COUNT(*) FILTER (WHERE position_count > 0) AS with_positions
       FROM (
           SELECT d.department_id, COUNT(p.position_id) AS position_count
             FROM department d
             LEFT JOIN position p ON p.department_id = d.department_id
            GROUP BY d.department_id
       ) department_counts'
)->fetch();

$returnQs = http_build_query(array_filter(
    ['q' => $q, 'p' => $pager['page']],
    static fn ($value) => $value !== ''
));

require SRC_PATH . '/views/partials/header.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Departments</h1>
        <p class="mt-1 text-sm text-gray-600">Organize the departments that offer job positions.</p>
    </div>
    <a href="<?= e(url('department_form')) ?>" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Add department</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2">
    <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
        <p class="text-sm text-gray-500">Total departments</p>
        <p class="mt-1 text-2xl font-semibold text-slate-800"><?= e($counts['total']) ?></p>
    </div>
    <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
        <p class="text-sm text-gray-500">Departments with positions</p>
        <p class="mt-1 text-2xl font-semibold text-indigo-600"><?= e($counts['with_positions']) ?></p>
    </div>
</div>

<form method="get" action="index.php" class="mt-6 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-end">
    <input type="hidden" name="page" value="departments">
    <div class="flex-1">
        <label for="q" class="block text-sm font-medium text-gray-700">Search departments</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Department name or description"
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Search</button>
        <?php if ($q !== ''): ?>
            <a href="<?= e(url('departments')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Department</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">Positions</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($departments as $department): ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= e($department['department_name']) ?></td>
                    <td class="px-4 py-3 text-gray-600">
                        <?= $department['description'] !== null && $department['description'] !== '' ? nl2br(e($department['description'])) : '<span class="text-gray-400">No description</span>' ?>
                    </td>
                    <td class="px-4 py-3">
                        <a class="text-indigo-600 hover:underline" href="<?= e(url('positions', ['department' => $department['department_id']])) ?>">
                            <?= e($department['position_count']) ?> <?= (int) $department['position_count'] === 1 ? 'position' : 'positions' ?>
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3">
                            <a href="<?= e(url('department_form', ['id' => $department['department_id']])) ?>" class="text-indigo-600 hover:underline">Edit</a>
                            <?php if ((int) $department['position_count'] === 0): ?>
                                <form method="post" action="index.php" class="inline" onsubmit="<?= js_confirm('Delete the department ' . $department['department_name'] . '? This cannot be undone.') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="department_delete">
                                    <input type="hidden" name="department_id" value="<?= e($department['department_id']) ?>">
                                    <input type="hidden" name="return_qs" value="<?= e($returnQs) ?>">
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-gray-400" title="This department has positions and cannot be deleted.">In use</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$departments): ?>
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No departments found. Add a department to start organizing job positions.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination('departments', ['q' => $q], $pager) ?>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
