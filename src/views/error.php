<?php
$pageTitle = 'Error';
$activeNav = '';
require SRC_PATH . '/views/partials/header.php';
?>
<div class="mx-auto max-w-xl rounded-lg bg-white p-8 text-center shadow-sm ring-1 ring-gray-200">
    <p class="text-5xl font-bold text-indigo-600"><?= e($status) ?></p>
    <p class="mt-4 text-gray-700"><?= e($message) ?></p>
    <a href="<?= e(url('dashboard')) ?>" class="mt-6 inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Back to dashboard</a>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
