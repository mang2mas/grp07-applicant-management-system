<?php

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

$interview = null;

if ($id !== false && $id !== null) {
    $interview = db_query(
        'SELECT interview_id, application_id, interview_date,
                interview_type, results, notes
           FROM interview
          WHERE interview_id = :id',
        [':id' => $id]
    )->fetch();

    if (!$interview) {
        throw new HttpException(404, 'Interview not found.');
    }
}

$isEdit = $interview !== null;

$pageTitle = $isEdit ? 'Edit Interview' : 'Schedule Interview';
$activeNav = 'interviews';

$applications = db_query(
    'SELECT ap.application_id,
            a.first_name,
            a.last_name,
            p.position_title,
            d.department_name,
            ap.application_date
       FROM application ap
       JOIN applicant a ON a.applicant_id = ap.applicant_id
       JOIN position p ON p.position_id = ap.position_id
       JOIN department d ON d.department_id = p.department_id
      ORDER BY ap.application_date DESC, ap.application_id DESC'
)->fetchAll();

[$errors, $old] = form_state_pull();

$values = $old ?: ($interview ?: []);

$val = static fn (string $key): string =>
    (string) ($values[$key] ?? '');

$interviewTypes = [
    'Phone',
    'Video',
    'In-person',
    'Panel',
    'Technical',
];

$results = [
    'Pending',
    'Passed',
    'Failed',
    'No Show',
    'Rescheduled',
];

// Convert PostgreSQL timestamp into datetime-local format.
$dateValue = $val('interview_date');

if ($dateValue !== '' && str_contains($dateValue, ' ')) {
    $dateValue = (new DateTimeImmutable($dateValue))->format('Y-m-d\TH:i');
}

require SRC_PATH . '/views/partials/header.php';
?>

<div class="mx-auto max-w-2xl">

    <a href="<?= e(url('interviews')) ?>"
       class="text-sm text-indigo-600 hover:underline">
        &larr; Back to interviews
    </a>

    <h1 class="mt-2 text-2xl font-semibold">
        <?= e($pageTitle) ?>
    </h1>

    <p class="mt-1 text-sm text-gray-600">
        Schedule an interview for an existing applicant application.
    </p>

    <?php if (!$applications): ?>

        <div class="mt-6 rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            There are no applications available.

            <a href="<?= e(url('application_form')) ?>"
               class="font-medium underline">
                Create an application
            </a>
            before scheduling an interview.
        </div>

    <?php else: ?>

        <form method="post"
              action="index.php"
              novalidate
              class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">

            <?= csrf_field() ?>

            <input type="hidden" name="action" value="interview_save">

            <?php if ($isEdit): ?>
                <input type="hidden" name="interview_id" value="<?= e($interview['interview_id']) ?>">
            <?php endif; ?>

            <!-- Application -->
            <div>
                <label for="application_id" class="block text-sm font-medium text-gray-700">
                    Applicant and position
                </label>

                <select
                    id="application_id"
                    name="application_id"
                    required
                    class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['application_id']) ? 'border-red-400' : 'border-gray-300' ?>"
                >
                    <option value="">Select an application...</option>

                    <?php foreach ($applications as $application): ?>
                        <option
                            value="<?= e($application['application_id']) ?>"
                            <?= (int) $application['application_id'] === (int) $val('application_id') ? 'selected' : '' ?>
                        >
                            <?= e(
                                $application['last_name'] . ', ' .
                                $application['first_name'] . ' — ' .
                                $application['position_title'] . ' (' .
                                $application['department_name'] . ')'
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if (isset($errors['application_id'])): ?>
                    <p class="mt-1 text-sm text-red-600">
                        <?= e($errors['application_id']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Date and time -->
            <div>
                <label for="interview_date" class="block text-sm font-medium text-gray-700">
                    Interview date and time
                </label>

                <input
                    type="datetime-local"
                    id="interview_date"
                    name="interview_date"
                    required
                    value="<?= e($dateValue) ?>"
                    class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['interview_date']) ? 'border-red-400' : 'border-gray-300' ?>"
                >

                <?php if (isset($errors['interview_date'])): ?>
                    <p class="mt-1 text-sm text-red-600">
                        <?= e($errors['interview_date']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Interview type and result -->
            <div class="grid gap-5 sm:grid-cols-2">

                <div>
                    <label for="interview_type" class="block text-sm font-medium text-gray-700">
                        Interview type
                    </label>

                    <select
                        id="interview_type"
                        name="interview_type"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >
                        <?php foreach ($interviewTypes as $type): ?>
                            <option
                                value="<?= e($type) ?>"
                                <?= ($val('interview_type') ?: 'In-person') === $type ? 'selected' : '' ?>
                            >
                                <?= e($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="result" class="block text-sm font-medium text-gray-700">
                        Interview result
                    </label>

                    <select
                        id="result"
                        name="result"
                        required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >
                        <?php foreach ($results as $result): ?>
                            <option
                                value="<?= e($result) ?>"
                                <?= ($val('result') ?: 'Pending') === $result ? 'selected' : '' ?>
                            >
                                <?= e($result) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    Interview notes (optional)
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    maxlength="10000"
                    placeholder="Enter interview feedback, observations, or other notes..."
                    class="mt-1 block w-full rounded-md border px-3 py-2 text-sm <?= isset($errors['notes']) ? 'border-red-400' : 'border-gray-300' ?>"
                ><?= e($val('notes')) ?></textarea>

                <?php if (isset($errors['notes'])): ?>
                    <p class="mt-1 text-sm text-red-600">
                        <?= e($errors['notes']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">

                <a href="<?= e(url('interviews')) ?>"
                   class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    <?= $isEdit ? 'Save Changes' : 'Schedule Interview' ?>
                </button>

            </div>

        </form>

    <?php endif; ?>

</div>

<?php require SRC_PATH . '/views/partials/footer.php'; ?>