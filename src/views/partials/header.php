<?php
/**
 * Shared page header + navigation.
 * Expects (optional): $pageTitle, $activeNav.
 */
$pageTitle = $pageTitle ?? 'Applicant Management System';
$activeNav = $activeNav ?? '';

$navItems = [
    'dashboard'    => ['Dashboard', url('dashboard')],
    'applicants'   => ['Applicants', url('applicants')],
    'applications' => ['Applications', url('applications')],
    'positions'    => ['Positions', url('positions')],
    'status'       => ['System Status', url('status')],
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
        <nav class="flex flex-wrap gap-1" aria-label="Main">
            <?php foreach ($navItems as $key => [$label, $href]): ?>
                <a href="<?= e($href) ?>"
                   class="rounded-md px-3 py-1.5 text-sm font-medium <?= $key === $activeNav ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>"
                   <?= $key === $activeNav ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-6">
    <?php foreach ($flashes as $flash): ?>
        <div class="mb-4 rounded-md border px-4 py-3 text-sm <?= e($flashStyles[$flash['type']] ?? $flashStyles['info']) ?>" role="alert">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>
