<?php
$pageTitle = 'System Status';
$activeNav = 'status';

$info = db_query('SELECT current_database() AS db, current_user AS usr, current_setting(\'server_version\') AS version')->fetch();

// Fixed list of table names (never user input), so interpolation is safe here.
$tables = ['department', 'position', 'applicant', 'application_status', 'application', 'interview', 'requirement', 'applicant_requirement'];
$checks = [];
foreach ($tables as $table) {
    try {
        $checks[$table] = (int) db_query('SELECT COUNT(*) FROM "' . $table . '"')->fetchColumn();
    } catch (PDOException $e) {
        // Missing table or no privilege (for example a read-only account).
        $checks[$table] = null;
    }
}

$details = [
    'Database connection' => 'Connected',
    'Database'            => $info['db'],
    'Connected as'        => $info['usr'],
    'PostgreSQL version'  => $info['version'],
    'PHP version'         => PHP_VERSION,
    'Server time'         => date('M j, Y g:i A') . ' (' . date_default_timezone_get() . ')',
];

require SRC_PATH . '/views/partials/header.php';
?>
<h1 class="text-2xl font-semibold">System Status</h1>
<p class="mt-1 text-sm text-gray-600">Connection details and a row count for every table.</p>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
        <h2 class="border-b border-gray-200 px-4 py-3 font-semibold">Environment</h2>
        <dl class="divide-y divide-gray-100 text-sm">
            <?php foreach ($details as $label => $value): ?>
                <div class="flex justify-between gap-4 px-4 py-2.5">
                    <dt class="text-gray-500"><?= e($label) ?></dt>
                    <dd class="text-right font-medium"><?= $label === 'Database connection' ? badge((string) $value, 'green') : e($value) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>

    <section class="rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
        <h2 class="border-b border-gray-200 px-4 py-3 font-semibold">Tables</h2>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr><th class="px-4 py-2">Table</th><th class="px-4 py-2">Check</th><th class="px-4 py-2 text-right">Rows</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($checks as $table => $count): ?>
                <tr>
                    <td class="px-4 py-2 font-mono text-xs"><?= e($table) ?></td>
                    <td class="px-4 py-2"><?= $count === null ? badge('No access / missing', 'red') : badge('OK', 'green') ?></td>
                    <td class="px-4 py-2 text-right"><?= $count === null ? '—' : e($count) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
