<?php
/**
 * Login screen (public). Rendered without the main navigation.
 */
$pageTitle = 'Sign in';
$activeNav = '';
$hideNav   = true;

[$errors, $old] = form_state_pull();

require SRC_PATH . '/views/partials/header.php';
?>
<div class="mx-auto mt-8 max-w-sm">
    <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <h1 class="text-xl font-semibold">Sign in</h1>
        <p class="mt-1 text-sm text-gray-600">Use your Applicant Management System account.</p>

        <form method="post" action="index.php" novalidate class="mt-6 space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="login_process">

            <?= form_input('username', 'Username', (string) ($old['username'] ?? ''), $errors, 'text', ['required' => true, 'maxlength' => '50', 'autocomplete' => 'username', 'autofocus' => true]) ?>
            <?= form_input('password', 'Password', '', $errors, 'password', ['required' => true, 'maxlength' => '200', 'autocomplete' => 'current-password']) ?>

            <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Sign in</button>
        </form>
    </div>
</div>
<?php require SRC_PATH . '/views/partials/footer.php'; ?>
