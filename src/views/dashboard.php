<?php
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$now       = date('Y-m-d H:i:s');

// "Active" applications are those not yet closed (not Accepted / Rejected).
$counts = db_query(
    "SELECT
        (SELECT COUNT(*) FROM applicant) AS applicants,
        (SELECT COUNT(*) FROM position WHERE status = 'Open') AS open_positions,
        (SELECT COUNT(*)
           FROM application ap
           JOIN application_status s ON s.status_id = ap.status_id
          WHERE s.status_name NOT IN ('Accepted', 'Rejected')) AS active_applications,
        (SELECT COUNT(*)
           FROM interview
          WHERE interview_date >= CAST(:now AS timestamp)
            AND COALESCE(results, 'Pending') = 'Pending') AS upcoming_interviews",
    [':now' => $now]
)->fetch();

$recentApplicants = db_query(
    'SELECT a.applicant_id, a.first_name, a.last_name, a.email, a.created_at, ls.status_name
       FROM applicant a ' . latest_status_join() . '
      ORDER BY a.created_at DESC, a.applicant_id DESC
      LIMIT 5'
)->fetchAll();

$recentApplications = db_query(
    'SELECT ap.application_id, ap.application_date, a.first_name, a.last_name,
            p.position_title, s.status_name
       FROM application ap
       JOIN applicant a           ON a.applicant_id = ap.applicant_id
       JOIN position p            ON p.position_id = ap.position_id
       JOIN application_status s  ON s.status_id = ap.status_id
      ORDER BY ap.application_date DESC, ap.application_id DESC
      LIMIT 5'
)->fetchAll();

$nextInterviews = db_query(
    'SELECT i.interview_id, i.interview_date, i.interview_type, i.results,
            a.first_name, a.last_name, p.position_title
       FROM interview i
       JOIN application ap ON ap.application_id = i.application_id
       JOIN applicant a    ON a.applicant_id = ap.applicant_id
       JOIN position p     ON p.position_id = ap.position_id
      WHERE i.interview_date >= CAST(:now AS timestamp)
      ORDER BY i.interview_date
      LIMIT 5',
    [':now' => $now]
)->fetchAll();

$cards = [
    ['Total Applicants',      (int) $counts['applicants'],          url('applicants'),                      'text-indigo-600'],
    ['Open Positions',        (int) $counts['open_positions'],      url('positions', ['status' => 'Open']), 'text-green-600'],
    ['Active Applications',   (int) $counts['active_applications'], url('applications'),                   'text-blue-600'],
    ['Upcoming Interviews',   (int) $counts['upcoming_interviews'], '#upcoming-interviews',                'text-purple-600'],
];

require SRC_PATH . '/views/partials/header.php';
?>
<h1 class="text-2xl font-semibold">Dashboard</h1>
<p class="mt-1 text-sm text-gray-600">Overview of recruitment activity.</p>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ($cards as [$label, $value, $href, $tone]): ?>
        <a href="<?= e($href) ?>" class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200 hover:ring-indigo-300">
            <p class="text-sm font-medium text-gray-500"><?= e($label) ?></p>
            <p class="mt-2 text-3xl font-semibold <?= e($tone) ?>"><?= e($value) ?></p>
        </a>
    <?php endforeach; ?>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section class="rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <h2 class="font-semibold">Recent applicants</h2>
            <a href="<?= e(url('applicants')) ?>" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Registered</th><th class="px-4 py-2">Latest status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($recentApplicants as $row): ?>
                    <tr>
                        <td class="px-4 py-2">
                            <a href="<?= e(can_write() ? url('applicant_form', ['id' => $row['applicant_id']]) : url('applicants', ['q' => $row['email']])) ?>" class="font-medium text-indigo-700 hover:underline"><?= e($row['first_name'] . ' ' . $row['last_name']) ?></a>
                            <div class="text-xs text-gray-500"><?= e($row['email']) ?></div>
                        </td>
                        <td class="px-4 py-2 text-gray-600"><?= e(format_date($row['created_at'])) ?></td>
                        <td class="px-4 py-2"><?= status_badge($row['status_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentApplicants): ?>
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No applicants yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <h2 class="font-semibold">Recent applications</h2>
            <a href="<?= e(url('applications')) ?>" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr><th class="px-4 py-2">Applicant</th><th class="px-4 py-2">Position</th><th class="px-4 py-2">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($recentApplications as $row): ?>
                    <tr>
                        <td class="px-4 py-2">
                            <?= e($row['first_name'] . ' ' . $row['last_name']) ?>
                            <div class="text-xs text-gray-500">Applied <?= e(format_date($row['application_date'])) ?></div>
                        </td>
                        <td class="px-4 py-2"><?= e($row['position_title']) ?></td>
                        <td class="px-4 py-2"><?= status_badge($row['status_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentApplications): ?>
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No applications yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<section id="upcoming-interviews" class="mt-6 rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <div class="border-b border-gray-200 px-4 py-3">
        <h2 class="font-semibold">Next interviews</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr><th class="px-4 py-2">When</th><th class="px-4 py-2">Applicant</th><th class="px-4 py-2">Position</th><th class="px-4 py-2">Type</th><th class="px-4 py-2">Result</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($nextInterviews as $row): ?>
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap"><?= e(format_datetime($row['interview_date'])) ?></td>
                    <td class="px-4 py-2"><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td class="px-4 py-2"><?= e($row['position_title']) ?></td>
                    <td class="px-4 py-2"><?= e($row['interview_type']) ?></td>
                    <td class="px-4 py-2"><?= result_badge($row['results']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$nextInterviews): ?>
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No upcoming interviews.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
