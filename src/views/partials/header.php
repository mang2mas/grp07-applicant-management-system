<?php
/**
 * Shared page header + navigation.
 * Expects (optional): $pageTitle, $activeNav.
 */
$pageTitle = $pageTitle ?? 'Applicant Management System';
$activeNav = $activeNav ?? '';
$hideNav   = $hideNav ?? false;   // true on the login screen

$navGroups = [
    'applicants' => [
        'label' => 'Applicants',
        'pages' => [
            'applicants' => ['Applicants', url('applicants')],
            'applicant_requirements' => ['Applicant Requirements', url('applicant_requirements')],
        ],
    ],

    'recruitment' => [
        'label' => 'Recruitment',
        'pages' => [
            'applications' => ['Applications', url('applications')],
            'positions' => ['Positions', url('positions')],
            'departments' => ['Departments', url('departments')],
            'requirements' => ['Requirements', url('requirements')],
            'interviews' => ['Interviews', url('interviews')],
        ],
    ],

    'system' => [
        'label' => 'System',
        'pages' => [
            'status' => ['System Status', url('status')],
        ],
    ],
];

$flashStyles = [
    'success' => 'border-green-200 bg-green-50 text-green-800',
    'error'   => 'border-red-200 bg-red-50 text-red-800',
    'info'    => 'border-blue-200 bg-blue-50 text-blue-800',
];
$flashes = flash_pull();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · Applicant Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
<header class="bg-slate-900 text-white shadow">
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="<?= e(url('dashboard')) ?>" class="text-lg font-semibold tracking-tight">
            AMS <span class="font-normal text-slate-400">· Applicant Management System</span>
        </a>
        <?php if (!$hideNav && is_logged_in()): ?>
        <nav class="flex flex-wrap items-center gap-2" aria-label="Main">

            <!-- Dashboard -->
            <a href="<?= e(url('dashboard')) ?>"
            class="rounded-md px-3 py-2 text-sm font-medium
            <?= $activeNav === 'dashboard'
                ? 'bg-slate-700 text-white'
                : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                Dashboard
            </a>

            <!-- Dropdown menus -->
            <?php foreach ($navGroups as $group): ?>

                <?php
                $isGroupActive = array_key_exists($activeNav, $group['pages']);
                ?>

                <details class="relative">
                    <summary
                        class="cursor-pointer list-none rounded-md px-3 py-2 text-sm font-medium
                        <?= $isGroupActive
                            ? 'bg-slate-700 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>"
                    >
                        <?= e($group['label']) ?>
                        <span class="ml-1 text-xs">▼</span>
                    </summary>

                    <div class="absolute left-0 z-50 mt-2 min-w-52 overflow-hidden rounded-md border border-slate-700 bg-slate-800 py-1 shadow-lg">

                        <?php foreach ($group['pages'] as $key => [$label, $href]): ?>

                            <a href="<?= e($href) ?>"
                            class="block px-4 py-2 text-sm
                            <?= $key === $activeNav
                                ? 'bg-slate-700 text-white'
                                : 'text-slate-300 hover:bg-slate-700 hover:text-white' ?>"
                            <?= $key === $activeNav ? 'aria-current="page"' : '' ?>>
                                <?= e($label) ?>
                            </a>

                        <?php endforeach; ?>

                    </div>
                </details>

            <?php endforeach; ?>

        </nav>
        <form method="post" action="index.php" class="flex items-center gap-3 text-sm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="logout">
            <span class="text-slate-300"><?= e($_SESSION['username'] ?? '') ?>
                <span class="ml-1 rounded-full bg-slate-700 px-2 py-0.5 text-xs text-slate-200"><?= e($_SESSION['role'] ?? '') ?></span></span>
            <button type="submit" class="rounded-md px-3 py-1.5 font-medium text-slate-300 ring-1 ring-inset ring-slate-600 hover:bg-slate-800 hover:text-white">Logout</button>
        </form>
        <?php endif; ?>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-6">
    <?php foreach ($flashes as $flash): ?>
        <div class="mb-4 rounded-md border px-4 py-3 text-sm <?= e($flashStyles[$flash['type']] ?? $flashStyles['info']) ?>" role="alert">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>
