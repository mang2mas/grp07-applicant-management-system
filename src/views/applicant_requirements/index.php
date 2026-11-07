<?php
$pageTitle = 'Applicant Requirements';
$activeNav = 'applicant_requirements';

// Load applicants for the selector.
$applicants = db_query(
    'SELECT applicant_id, first_name, last_name, email
       FROM applicant
      ORDER BY last_name, first_name'
)->fetchAll();

// Read the selected applicant ID.
$rawApplicantId = $_GET['applicant_id'] ?? '';
$selectedApplicantId = null;

if ($rawApplicantId !== '') {
    $selectedApplicantId = filter_var($rawApplicantId, FILTER_VALIDATE_INT);

    if ($selectedApplicantId === false || $selectedApplicantId < 1) {
        throw new HttpException(400, 'Invalid applicant ID.');
    }
}

// Load the selected applicant.
$selectedApplicant = null;
$requirements = [];

if ($selectedApplicantId !== null) {
    $selectedApplicant = db_query(
        'SELECT applicant_id, first_name, last_name, email
           FROM applicant
          WHERE applicant_id = :id',
        [':id' => $selectedApplicantId]
    )->fetch();

    if (!$selectedApplicant) {
        throw new HttpException(404, 'Applicant not found.');
    }

    // Load every requirement and check whether it is assigned
    // to the selected applicant.
    $requirements = db_query(
        'SELECT r.requirement_id,
                r.requirement_name,
                r.description,
                r.is_required,
                ar.status,
                ar.submitted_date,
                ar.remarks
           FROM requirement r
           LEFT JOIN applicant_requirement ar
             ON ar.requirement_id = r.requirement_id
            AND ar.applicant_id = :applicant_id
          ORDER BY r.requirement_name',
        [':applicant_id' => $selectedApplicantId]
    )->fetchAll();
}

$assignedCount = 0;

foreach ($requirements as $requirement) {
    if ($requirement['status'] !== null) {
        $assignedCount++;
    }
}

$totalRequirements = count($requirements);
$pendingAssignmentCount = $totalRequirements - $assignedCount;

require SRC_PATH . '/views/partials/header.php';
?>

<div>
    <h1 class="text-2xl font-semibold">Applicant Requirements</h1>
    <p class="mt-1 text-sm text-gray-600">
        Assign requirements to applicants and monitor their submission progress.
    </p>
</div>

<!-- Applicant selector -->
<div class="mt-6 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
    <form method="get" action="index.php" class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <input type="hidden" name="page" value="applicant_requirements">

        <div class="flex-1">
            <label for="applicant_id" class="block text-sm font-medium text-gray-700">
                Select applicant
            </label>

            <select
                id="applicant_id"
                name="applicant_id"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
            >
                <option value="">Choose an applicant</option>

                <?php foreach ($applicants as $applicant): ?>
                    <option
                        value="<?= e($applicant['applicant_id']) ?>"
                        <?= (int) $applicant['applicant_id'] === $selectedApplicantId ? 'selected' : '' ?>
                    >
                        <?= e($applicant['last_name'] . ', ' . $applicant['first_name']) ?>
                        — <?= e($applicant['email']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button
            type="submit"
            class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900"
        >
            View requirements
        </button>
    </form>

    <?php if (!$applicants): ?>
        <p class="mt-3 text-sm text-amber-700">
            No applicants have been registered yet. Add an applicant before tracking requirements.
        </p>
    <?php endif; ?>
</div>

<?php if ($selectedApplicant): ?>

    <?php
    $fullName = $selectedApplicant['first_name'] . ' ' . $selectedApplicant['last_name'];
    ?>

    <!-- Selected applicant information -->
    <div class="mt-6 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
        <p class="text-sm text-gray-500">Currently selected applicant</p>
        <h2 class="mt-1 text-lg font-semibold text-slate-800">
            <?= e($fullName) ?>
        </h2>
        <p class="text-sm text-gray-600"><?= e($selectedApplicant['email']) ?></p>
    </div>

    <!-- Requirement summary -->
    <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Total requirements</p>
            <p class="mt-1 text-2xl font-semibold text-slate-800">
                <?= e($totalRequirements) ?>
            </p>
        </div>

        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Assigned</p>
            <p class="mt-1 text-2xl font-semibold text-indigo-600">
                <?= e($assignedCount) ?>
            </p>
        </div>

        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm text-gray-500">Not yet assigned</p>
            <p class="mt-1 text-2xl font-semibold text-amber-600">
                <?= e($pendingAssignmentCount) ?>
            </p>
        </div>
    </div>

    <!-- Requirements table -->
    <div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-200 px-4 py-3">
            <h3 class="font-semibold text-slate-800">Requirements checklist</h3>
            <p class="mt-1 text-sm text-gray-500">
                Assign requirements and track document submissions for this applicant.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Requirement</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Submission status</th>
                        <th class="px-4 py-3">Submitted date</th>
                        <th class="px-4 py-3">Remarks</th>
                        <th class="px-4 py-3 text-right"><?= is_viewer() ? '' : 'Action' ?></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($requirements as $requirement): ?>
                        <?php $isAssigned = $requirement['status'] !== null; ?>

                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    <?= e($requirement['requirement_name']) ?>
                                </div>

                                <?php if (!empty($requirement['description'])): ?>
                                    <div class="mt-1 text-xs text-gray-500">
                                        <?= e($requirement['description']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3">
                                <?php if ($requirement['is_required']): ?>
                                    <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                        Required
                                    </span>
                                <?php else: ?>
                                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                        Optional
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3">
                                <?php if ($isAssigned): ?>
                                    <?php
                                    $statusTone = match ($requirement['status']) {
                                        'Verified' => 'bg-green-50 text-green-700',
                                        'Submitted' => 'bg-blue-50 text-blue-700',
                                        'Rejected' => 'bg-red-50 text-red-700',
                                        default => 'bg-amber-50 text-amber-700',
                                    };
                                    ?>
                                    <span class="rounded-full px-2 py-1 text-xs font-medium <?= e($statusTone) ?>">
                                        <?= e($requirement['status']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">Not assigned</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                <?= $requirement['submitted_date']
                                    ? e(format_date($requirement['submitted_date']))
                                    : '<span class="text-gray-400">—</span>' ?>
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                <?= !empty($requirement['remarks'])
                                    ? e($requirement['remarks'])
                                    : '<span class="text-gray-400">—</span>' ?>
                            </td>

                            <td class="px-4 py-3">
                                <?php if (!is_viewer()): ?><?php if (!$isAssigned): ?>

                                    <form method="post" action="index.php">
                                        <?= csrf_field() ?>

                                        <input type="hidden" name="action" value="applicant_requirement_assign">
                                        <input type="hidden" name="applicant_id" value="<?= e($selectedApplicantId) ?>">
                                        <input type="hidden" name="requirement_id" value="<?= e($requirement['requirement_id']) ?>">

                                        <button
                                            type="submit"
                                            class="text-indigo-600 hover:underline"
                                        >
                                            Assign
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <form method="post" action="index.php" class="min-w-48 space-y-2">
                                        <?= csrf_field() ?>

                                        <input type="hidden" name="action" value="applicant_requirement_update">
                                        <input type="hidden" name="applicant_id" value="<?= e($selectedApplicantId) ?>">
                                        <input type="hidden" name="requirement_id" value="<?= e($requirement['requirement_id']) ?>">

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">
                                                Update status
                                            </label>

                                            <select
                                                name="status"
                                                required
                                                class="mt-1 w-full rounded-md border border-gray-300 px-2 py-2 text-sm"
                                            >
                                                <?php foreach (['Pending', 'Submitted', 'Verified', 'Rejected'] as $status): ?>
                                                    <option
                                                        value="<?= e($status) ?>"
                                                        <?= $requirement['status'] === $status ? 'selected' : '' ?>
                                                    >
                                                        <?= e($status) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">
                                                Submission date
                                            </label>

                                            <input
                                                type="date"
                                                name="submitted_date"
                                                value="<?= e($requirement['submitted_date'] ?? '') ?>"
                                                class="mt-1 w-full rounded-md border border-gray-300 px-2 py-2 text-sm"
                                            >
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">
                                                Remarks
                                            </label>

                                            <textarea
                                                name="remarks"
                                                rows="2"
                                                class="mt-1 w-full rounded-md border border-gray-300 px-2 py-2 text-sm"
                                                placeholder="Add remarks..."
                                            ><?= e($requirement['remarks'] ?? '') ?></textarea>
                                        </div>

                                        <button
                                            type="submit"
                                            class="w-full rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900"
                                        >
                                            Save Changes
                                        </button>
                                    </form>

                                    <form
                                        method="post"
                                        action="index.php"
                                        class="mt-2"
                                        onsubmit="return confirm('Remove this requirement from the applicant?');"
                                    >
                                        <?= csrf_field() ?>

                                        <input type="hidden" name="action" value="applicant_requirement_remove">
                                        <input type="hidden" name="applicant_id" value="<?= e($selectedApplicantId) ?>">
                                        <input type="hidden" name="requirement_id" value="<?= e($requirement['requirement_id']) ?>">

                                        <button
                                            type="submit"
                                            class="w-full rounded-md border border-red-300 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50"
                                        >
                                            Remove Assignment
                                        </button>
                                    </form>

                                <?php endif; ?><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$requirements): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No requirements have been created yet. Add requirements first.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-white px-4 py-10 text-center">
        <h2 class="font-medium text-slate-800">Select an applicant to get started</h2>
        <p class="mt-1 text-sm text-gray-500">
            Choose an applicant above to view and manage their requirements.
        </p>
    </div>

<?php endif; ?>

<?php require SRC_PATH . '/views/partials/footer.php'; ?>