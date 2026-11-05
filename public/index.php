<?php
/**
 * AMS front controller - the only script reachable from the web.
 *
 *   GET  index.php?page=<name>      renders a view from src/views/
 *   POST index.php (action=<name>)  runs a handler from src/actions/ (CSRF-checked)
 *
 * Only whitelisted pages/actions can be loaded, so user input never
 * decides which file is included.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$pages = [
    'dashboard'              => 'dashboard.php',
    'applicants'             => 'applicants/index.php',
    'applicant_form'         => 'applicants/form.php',
    'applications'           => 'applications/index.php',
    'application_form'       => 'applications/form.php',
    'positions'              => 'positions/index.php',
    'departments'            => 'departments/index.php',
    'department_form'        => 'departments/form.php',
    'position_form'          => 'positions/form.php',
    'requirements'           => 'requirements/index.php',
    'requirement_form'       => 'requirements/form.php',
    'applicant_requirements' => 'applicant_requirements/index.php',
    'interviews'             => 'interviews/index.php',
    'interview_form'         => 'interviews/form.php',
    'status'                 => 'status.php',
];

$actions = [
    'applicant_save'               => 'applicant_save.php',
    'position_save'                => 'position_save.php',
    'position_delete'              => 'position_delete.php',
    'department_save'              => 'department_save.php',
    'department_delete'            => 'department_delete.php',
    'applicant_delete'             => 'applicant_delete.php',
    'application_create'           => 'application_create.php',
    'application_status'           => 'application_status.php',
    'application_delete'           => 'application_delete.php',
    'requirement_save'             => 'requirement_save.php',
    'requirement_delete'           => 'requirement_delete.php',
    'applicant_requirement_assign' => 'applicant_requirement_assign.php',
    'applicant_requirement_update' => 'applicant_requirement_update.php',
    'applicant_requirement_remove' => 'applicant_requirement_remove.php',
    'interview_save'               => 'interview_save.php',
    'interview_delete'             => 'interview_delete.php',
];

ob_start();

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'POST') {
        csrf_verify();
        $action = (string) ($_POST['action'] ?? '');
        if (!isset($actions[$action])) {
            throw new HttpException(400, 'Unknown action.');
        }
        require SRC_PATH . '/actions/' . $actions[$action];
        exit; // Handlers always redirect; this is a safety net.
    }

    if ($method !== 'GET' && $method !== 'HEAD') {
        throw new HttpException(405, 'Method not allowed.');
    }

    $page = (string) ($_GET['page'] ?? 'dashboard');
    if (!isset($pages[$page])) {
        throw new HttpException(404, 'Page not found.');
    }

    render($pages[$page]);
    ob_end_flush();
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_clean();
    }

    if ($e instanceof HttpException) {
        $status  = $e->getStatus();
        $message = $e->getMessage();
    } else {
        // Connection/config errors from config/database.php carry safe, generic messages.
        $safe = $e instanceof RuntimeException
            && (str_starts_with($e->getMessage(), 'Database configuration')
                || str_starts_with($e->getMessage(), 'Unable to connect'));

        if (!$safe) {
            error_log('[AMS] ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
        $status  = $safe ? 503 : 500;
        $message = $safe ? $e->getMessage() : 'Something went wrong. The details were written to the server log.';
    }

    http_response_code($status);
    render('error.php', ['status' => $status, 'message' => $message]);
    ob_end_flush();
}
