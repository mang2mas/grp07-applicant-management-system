<?php
$pageTitle = 'Applications';
$activeNav = 'applications';

$statusId   = filter_var($_GET['status_id'] ?? '', FILTER_VALIDATE_INT) ?: 0;
$positionId = filter_var($_GET['position_id'] ?? '', FILTER_VALIDATE_INT) ?: 0;

$statuses  = db_query('SELECT status_id, status_name FROM application_status ORDER BY status_id')->fetchAll();
$positions = db_query(
    'SELECT p.position_id, p.position_title, d.department_name
       FROM position p JOIN department d ON d.department_id = p.department_id
      ORDER BY p.position_title'
)->fetchAll();

$where  = [];
$params = [];
if ($statusId > 0) {
    $where[] = 'ap.status_id = :status_id';
    $params[':status_id'] = $statusId;
}
if ($positionId > 0) {
    $where[] = 'ap.position_id = :position_id';
    $params[':position_id'] = $positionId;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$fromSql  = 'FROM application ap
             JOIN applicant a          ON a.applicant_id = ap.applicant_id
             JOIN position p           ON p.position_id = ap.position_id
             JOIN department d         ON d.department_id = p.department_id
             JOIN application_status s ON s.status_id = ap.status_id';

$total = (int) db_query("SELECT COUNT(*) $fromSql $whereSql", $params)->fetchColumn();
$pager = paginate($total, (int) ($_GET['p'] ?? 1), 10);

$rows = db_query(
    "SELECT ap.application_id, ap.application_date, ap.status_id,
            a.applicant_id, a.first_name, a.last_name,
            p.position_id, p.position_title, d.department_name, s.status_name,
            (SELECT COUNT(*) FROM interview i WHERE i.application_id = ap.application_id) AS interview_count
       $fromSql $whereSql
      ORDER BY ap.application_date DESC, ap.application_id DESC
      LIMIT :limit OFFSET :offset",
    $params + [':limit' => $pager['per_page'], ':offset' => $pager['offset']]
)->fetchAll();

$filterParams = ['status_id' => $statusId ?: '', 'position_id' => $positionId ?: ''];
$returnQs     = http_build_query(array_filter($filterParams + ['p' => $pager['page']], static fn ($v) => $v !== ''));

require SRC_PATH . '/views/partials/header.php';
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Applications</h1>
        <p class="mt-1 text-sm text-gray-600">Track each application by position and move it through the hiring stages.</p>
    </div>
    <?php if (!is_viewer()): ?><a href="<?= e(url('application_form')) ?>" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ New application</a><?php endif; ?>
</div>

<form method="get" action="index.php" class="mt-6 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-end">
    <input type="hidden" name="page" value="applications">
    <div>
        <label for="status_id" class="block text-sm font-medium text-gray-700">Status</label>
        <select id="status_id" name="status_id" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s['status_id']) ?>" <?= (int) $s['status_id'] === $statusId ? 'selected' : '' ?>><?= e($s['status_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex-1">
        <label for="position_id" class="block text-sm font-medium text-gray-700">Position</label>
        <select id="position_id" name="position_id" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All positions</option>
            <?php foreach ($positions as $p): ?>
                <option value="<?= e($p['position_id']) ?>" <?= (int) $p['position_id'] === $positionId ? 'selected' : '' ?>><?= e($p['position_title'] . ' — ' . $p['department_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Filter</button>
        <?php if ($statusId || $positionId): ?>
            <a href="<?= e(url('applications')) ?>" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Applicant</th>
                    <th class="px-4 py-3">Position</th>
                    <th class="px-4 py-3">Applied</th>
                    <th class="px-4 py-3">Interviews</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Change status</th>
                    <th class="px-4 py-3 text-right"><?= is_viewer() ? '' : 'Actions' ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($rows as $row): ?>
                <?php $fullName = $row['first_name'] . ' ' . $row['last_name']; ?>
                <tr>
                    <td class="px-4 py-3 font-medium"><?= e($fullName) ?></td>
                    <td class="px-4 py-3">
                        <?= e($row['position_title']) ?>
                        <div class="text-xs text-gray-500"><?= e($row['department_name']) ?></div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?= e(format_date($row['application_date'])) ?></td>
                    <td class="px-4 py-3"><?= e($row['interview_count']) ?></td>
                    <td class="px-4 py-3"><?= status_badge($row['status_name']) ?></td>
                    <td class="px-4 py-3">
                        <?php if (!is_viewer()): ?><form method="post" action="index.php" class="flex items-center gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="application_status">
                            <input type="hidden" name="application_id" value="<?= e($row['application_id']) ?>">
                            <input type="hidden" name="return_qs" value="<?= e($returnQs) ?>">
                            <label class="sr-only" for="st-<?= e($row['application_id']) ?>">Status for <?= e($fullName) ?></label>
                            <select id="st-<?= e($row['application_id']) ?>" name="status_id" class="rounded-md border border-gray-300 px-2 py-1 text-sm">
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= e($s['status_id']) ?>" <?= (int) $s['status_id'] === (int) $row['status_id'] ? 'selected' : '' ?>><?= e($s['status_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="rounded-md bg-slate-800 px-2.5 py-1 text-xs font-medium text-white hover:bg-slate-900">Update</button>
                        </form><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <?php if (!is_viewer()): ?><form method="post" action="index.php" class="inline"
                              onsubmit="<?= js_confirm('Delete the application of ' . $fullName . ' for ' . $row['position_title'] . '? Its interviews will also be deleted.') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="application_delete">
                            <input type="hidden" name="application_id" value="<?= e($row['application_id']) ?>">
                            <input type="hidden" name="return_qs" value="<?= e($returnQs) ?>">
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No applications match the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination('applications', $filterParams, $pager) ?>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
