<?php
$pageTitle = 'Interviews';
$activeNav = 'interviews';

$q = trim((string) ($_GET['q'] ?? ''));
$resultFilter = trim((string) ($_GET['result'] ?? ''));

$allowedResults = ['Pending', 'Passed', 'Failed', 'No Show', 'Rescheduled'];

if ($resultFilter !== '' && !in_array($resultFilter, $allowedResults, true)) {
    $resultFilter = '';
}

$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(
        a.first_name ILIKE :search1
        OR a.last_name ILIKE :search2
        OR p.position_title ILIKE :search3
    )";

    $search = '%' . like_escape($q) . '%';

    $params[':search1'] = $search;
    $params[':search2'] = $search;
    $params[':search3'] = $search;
}

if ($resultFilter !== '') {
    $where[] = 'i.results = :result';
    $params[':result'] = $resultFilter;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$fromSql = '
    FROM interview i
    JOIN application ap ON ap.application_id = i.application_id
    JOIN applicant a ON a.applicant_id = ap.applicant_id
    JOIN position p ON p.position_id = ap.position_id
    JOIN department d ON d.department_id = p.department_id
';

$total = (int) db_query(
    "SELECT COUNT(*) $fromSql $whereSql",
    $params
)->fetchColumn();

$pager = paginate($total, (int) ($_GET['p'] ?? 1), 10);

$interviews = db_query(
    "SELECT i.interview_id,
            i.application_id,
            i.interview_date,
            i.interview_type,
            i.results,
            i.notes,
            a.first_name,
            a.last_name,
            p.position_title,
            d.department_name
       $fromSql
       $whereSql
      ORDER BY i.interview_date DESC, i.interview_id DESC
      LIMIT :limit OFFSET :offset",
    $params + [
        ':limit' => $pager['per_page'],
        ':offset' => $pager['offset'],
    ]
)->fetchAll();

$filterParams = [
    'q' => $q,
    'result' => $resultFilter,
];

require SRC_PATH . '/views/partials/header.php';
?>

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold">Interview Management</h1>
        <p class="mt-1 text-sm text-gray-600">
            Schedule interviews and monitor their results.
        </p>
    </div>

    <?php if (!is_viewer()): ?><a href="<?= e(url('interview_form')) ?>"
       class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        + Schedule Interview
    </a><?php endif; ?>
</div>

<!-- Search and filters -->
<form method="get" action="index.php"
      class="mt-6 flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-end">

    <input type="hidden" name="page" value="interviews">

    <div class="flex-1">
        <label for="q" class="block text-sm font-medium text-gray-700">
            Search
        </label>

        <input
            type="search"
            id="q"
            name="q"
            value="<?= e($q) ?>"
            placeholder="Applicant or position..."
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
        >
    </div>

    <div>
        <label for="result" class="block text-sm font-medium text-gray-700">
            Interview result
        </label>

        <select
            id="result"
            name="result"
            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
        >
            <option value="">All results</option>

            <?php foreach ($allowedResults as $result): ?>
                <option value="<?= e($result) ?>"
                    <?= $resultFilter === $result ? 'selected' : '' ?>>
                    <?= e($result) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="flex gap-2">
        <button type="submit"
                class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
            Filter
        </button>

        <?php if ($q !== '' || $resultFilter !== ''): ?>
            <a href="<?= e(url('interviews')) ?>"
               class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300">
                Reset
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Interviews table -->
<div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">

            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Applicant</th>
                    <th class="px-4 py-3">Position</th>
                    <th class="px-4 py-3">Interview Date</th>
                    <th class="px-4 py-3">Interview Type</th>
                    <th class="px-4 py-3">Result</th>
                    <th class="px-4 py-3 text-right"><?= is_viewer() ? '' : 'Actions' ?></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                <?php foreach ($interviews as $interview): ?>

                    <?php
                    $resultTone = match ($interview['results']) {
                        'Passed' => 'bg-green-50 text-green-700',
                        'Failed' => 'bg-red-50 text-red-700',
                        'No Show' => 'bg-gray-100 text-gray-700',
                        'Rescheduled' => 'bg-blue-50 text-blue-700',
                        default => 'bg-amber-50 text-amber-700',
                    };
                    ?>

                    <tr>
                        <td class="px-4 py-3 font-medium">
                            <?= e($interview['first_name'] . ' ' . $interview['last_name']) ?>
                        </td>

                        <td class="px-4 py-3">
                            <?= e($interview['position_title']) ?>
                            <div class="text-xs text-gray-500">
                                <?= e($interview['department_name']) ?>
                            </div>
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap">
                            <?= e(format_datetime($interview['interview_date'])) ?>
                        </td>

                        <td class="px-4 py-3">
                            <?= e($interview['interview_type']) ?>
                        </td>

                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-medium <?= e($resultTone) ?>">
                                <?= e($interview['results'] ?? 'Pending') ?>
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <?php if (!is_viewer()): ?><a href="<?= e(url('interview_form', [
                                'id' => $interview['interview_id']
                            ])) ?>"
                               class="text-indigo-600 hover:underline">
                                Edit
                            </a>

                            <span class="mx-1 text-gray-300">|</span>

                            <form method="post" action="index.php" class="inline"
                                  onsubmit="<?= js_confirm('Delete this interview schedule?') ?>">

                                <?= csrf_field() ?>

                                <input type="hidden" name="action" value="interview_delete">
                                <input type="hidden" name="interview_id" value="<?= e($interview['interview_id']) ?>">

                                <button type="submit"
                                        class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </form><?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                <?php if (!$interviews): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            No interview records found.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>

    <?= render_pagination('interviews', $filterParams, $pager) ?>
</div>

<?php require SRC_PATH . '/views/partials/footer.php'; ?>