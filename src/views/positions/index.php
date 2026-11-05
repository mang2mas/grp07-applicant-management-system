<?php
$pageTitle = 'Positions';
$activeNav = 'positions';

$q = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$departmentFilter = filter_var($_GET['department'] ?? '', FILTER_VALIDATE_INT);
$departmentFilter = ($departmentFilter !== false && $departmentFilter > 0) ? $departmentFilter : null;

$validStatuses = ['Open', 'Closed', 'On Hold'];
if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}
$departments = db_query('SELECT department_id, department_name FROM department ORDER BY department_name')->fetchAll();

$where = [];
$params = [];
if ($q !== '') {
    $where[] = "(p.position_title ILIKE :q1 OR p.description ILIKE :q2 OR d.department_name ILIKE :q3)";
    $like = '%' . like_escape($q) . '%';
    $params += [':q1' => $like, ':q2' => $like, ':q3' => $like];
}
if ($statusFilter !== '') {
    $where[] = 'p.status = :status';
    $params[':status'] = $statusFilter;
}
if ($departmentFilter !== null) {
    $where[] = 'p.department_id = :department';
    $params[':department'] = $departmentFilter;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$fromSql = 'FROM position p JOIN department d ON d.department_id = p.department_id';

$total = (int) db_query("SELECT COUNT(*) $fromSql $whereSql", $params)->fetchColumn();
$pager = paginate($total, (int) ($_GET['p'] ?? 1), 10);
$rows = db_query(
    "SELECT p.position_id, p.position_title, p.description, p.employment_type, p.status,
            d.department_name,
            (SELECT COUNT(*) FROM application ap WHERE ap.position_id = p.position_id) AS application_count
       $fromSql $whereSql
      ORDER BY CASE p.status WHEN 'Open' THEN 1 WHEN 'On Hold' THEN 2 ELSE 3 END,
               d.department_name, p.position_title
      LIMIT :limit OFFSET :offset",
    $params + [':limit' => $pager['per_page'], ':offset' => $pager['offset']]
)->fetchAll();

$counts = db_query(
    "SELECT COUNT(*) AS total,
            COUNT(*) FILTER (WHERE status = 'Open') AS open,
            COUNT(*) FILTER (WHERE status = 'Closed') AS closed,
            COUNT(*) FILTER (WHERE status = 'On Hold') AS on_hold
       FROM position"
)->fetch();

$filterParams = ['q' => $q, 'status' => $statusFilter, 'department' => $departmentFilter ?? ''];
$returnQs = http_build_query(array_filter($filterParams + ['p' => $pager['page']], static fn ($v) => $v !== ''));

require SRC_PATH . '/views/partials/header.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Positions</h1>
        <p class="mt-1 text-sm text-gray-600">Create and manage job openings across departments.</p>
    </div>
    <a href="<?= e(url('position_form')) ?>" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Add position</a>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ([['All positions', $counts['total'], 'text-slate-800'], ['Open', $counts['open'], 'text-green-600'], ['On hold', $counts['on_hold'], 'text-yellow-600'], ['Closed', $counts['closed'], 'text-red-600']] as [$label, $value, $tone]): ?>
        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500"><?= e($label) ?></p>
            <p class="mt-1 text-2xl font-semibold <?= e($tone) ?>"><?= e($value) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<form method="get" action="index.php" class="mt-6 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-end">
    <input type="hidden" name="page" value="positions">
    <div class="flex-1">
        <label for="q" class="block text-sm font-medium text-gray-700">Search</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Position title, description, or department"
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
        <select id="department" name="department" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            <option value="">All departments</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= e($d['department_id']) ?>" <?= (int) $d['department_id'] === (int) $departmentFilter ? 'selected' : '' ?>><?= e($d['department_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select id="status" name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <?php foreach ($validStatuses as $s): ?>
                <option value="<?= e($s) ?>" <?= $s === $statusFilter ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Filter</button>
        <?php if ($q !== '' || $statusFilter !== '' || $departmentFilter !== null): ?>
            <a href="<?= e(url('positions')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Position</th><th class="px-4 py-3">Department</th>
                    <th class="px-4 py-3">Employment type</th><th class="px-4 py-3">Applications</th>
                    <th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-medium"><?= e($row['position_title']) ?></div>
                        <?php if ($row['description']): ?><div class="mt-1 max-w-md text-xs text-gray-500"><?= e($row['description']) ?></div><?php endif; ?>
                    </td>
                    <td class="px-4 py-3"><?= e($row['department_name']) ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= e($row['employment_type']) ?></td>
                    <td class="px-4 py-3"><?= e($row['application_count']) ?></td>
                    <td class="px-4 py-3"><?= position_badge($row['status']) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3">
                            <a href="<?= e(url('position_form', ['id' => $row['position_id']])) ?>" class="text-indigo-600 hover:underline">Edit</a>
                            <?php if ((int) $row['application_count'] === 0): ?>
                                <form method="post" action="index.php" class="inline" onsubmit="<?= js_confirm('Delete the position ' . $row['position_title'] . '? This cannot be undone.') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="position_delete">
                                    <input type="hidden" name="position_id" value="<?= e($row['position_id']) ?>">
                                    <input type="hidden" name="return_qs" value="<?= e($returnQs) ?>">
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-gray-400" title="This position has applications and cannot be deleted.">In use</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No positions match your filters.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination('positions', $filterParams, $pager) ?>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
