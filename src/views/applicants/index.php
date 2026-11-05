<?php
$pageTitle = 'Applicants';
$activeNav = 'applicants';

$q            = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$statuses     = db_query('SELECT status_name FROM application_status ORDER BY status_id')->fetchAll(PDO::FETCH_COLUMN);
if ($statusFilter !== '' && !in_array($statusFilter, $statuses, true)) {
    $statusFilter = '';
}

$where  = [];
$params = [];
if ($q !== '') {
    $like    = '%' . like_escape($q) . '%';
    $where[] = "((a.first_name || ' ' || a.last_name) ILIKE :q1 OR a.email ILIKE :q2 OR a.phone ILIKE :q3)";
    $params[':q1'] = $like;
    $params[':q2'] = $like;
    $params[':q3'] = $like;
}
if ($statusFilter !== '') {
    $where[] = 'ls.status_name = :status';
    $params[':status'] = $statusFilter;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$fromSql  = 'FROM applicant a ' . latest_status_join();

$total = (int) db_query("SELECT COUNT(*) $fromSql $whereSql", $params)->fetchColumn();
$pager = paginate($total, (int) ($_GET['p'] ?? 1), 10);

$rows = db_query(
    "SELECT a.applicant_id, a.first_name, a.last_name, a.email, a.phone, a.created_at,
            ls.status_name,
            (SELECT COUNT(*) FROM application x WHERE x.applicant_id = a.applicant_id) AS app_count
       $fromSql $whereSql
      ORDER BY a.last_name, a.first_name, a.applicant_id
      LIMIT :limit OFFSET :offset",
    $params + [':limit' => $pager['per_page'], ':offset' => $pager['offset']]
)->fetchAll();

$filterParams = ['q' => $q, 'status' => $statusFilter];
$returnQs     = http_build_query(array_filter($filterParams + ['p' => $pager['page']], static fn ($v) => $v !== ''));

require SRC_PATH . '/views/partials/header.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Applicants</h1>
        <p class="mt-1 text-sm text-gray-600">Search, register, edit, and remove applicant records.</p>
    </div>
    <?php if (!is_viewer()): ?><a href="<?= e(url('applicant_form')) ?>" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Register applicant</a><?php endif; ?>
</div>

<form method="get" action="index.php" class="mt-6 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-end">
    <input type="hidden" name="page" value="applicants">
    <div class="flex-1">
        <label for="q" class="block text-sm font-medium text-gray-700">Search</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Name, email, or phone"
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Latest status</label>
        <select id="status" name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All</option>
            <?php foreach ($statuses as $name): ?>
                <option value="<?= e($name) ?>" <?= $name === $statusFilter ? 'selected' : '' ?>><?= e($name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Filter</button>
        <?php if ($q !== '' || $statusFilter !== ''): ?>
            <a href="<?= e(url('applicants')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Applications</th>
                    <th class="px-4 py-3">Latest status</th>
                    <th class="px-4 py-3">Registered</th>
                    <th class="px-4 py-3 text-right"><?= is_viewer() ? '' : 'Actions' ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($rows as $row): ?>
                <?php $fullName = $row['first_name'] . ' ' . $row['last_name']; ?>
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-medium"><?= e($fullName) ?></div>
                        <div class="text-xs text-gray-500"><?= e($row['email']) ?></div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= e($row['phone']) ?></td>
                    <td class="px-4 py-3"><?= e($row['app_count']) ?></td>
                    <td class="px-4 py-3"><?= status_badge($row['status_name']) ?></td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?= e(format_date($row['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <?php if (!is_viewer()): ?><div class="flex items-center justify-end gap-3">
                            <a href="<?= e(url('applicant_form', ['id' => $row['applicant_id']])) ?>" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="post" action="index.php" class="inline"
                                  onsubmit="<?= js_confirm('Delete ' . $fullName . '? This also permanently deletes their ' . $row['app_count'] . ' application(s), interviews, and requirement records.') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="applicant_delete">
                                <input type="hidden" name="applicant_id" value="<?= e($row['applicant_id']) ?>">
                                <input type="hidden" name="return_qs" value="<?= e($returnQs) ?>">
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </div><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No applicants match your search.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination('applicants', $filterParams, $pager) ?>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
