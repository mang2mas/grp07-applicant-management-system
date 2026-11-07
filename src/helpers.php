<?php
/**
 * AMS helper functions: escaping, URLs, flash messages, CSRF, form state,
 * pagination, badges and small database shortcuts.
 */

declare(strict_types=1);

/** Exception carrying an HTTP status code, rendered by the front controller. */
class HttpException extends RuntimeException
{
    public function __construct(private int $status, string $message = '')
    {
        parent::__construct($message);
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}

/* ------------------------------------------------------------------ */
/* Output, URLs, redirects                                            */
/* ------------------------------------------------------------------ */

/** Escape a value for safe HTML output (XSS protection). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build a relative URL to a page of the front controller. */
function url(string $page, array $params = []): string
{
    $params = array_filter($params, static fn ($v) => $v !== '' && $v !== null);
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

/**
 * Rebuild a list URL from the hidden "return_qs" field of a POST form, keeping
 * only whitelisted parameters (no open redirect possible).
 */
function return_url(string $page, array $allowedKeys): string
{
    parse_str((string) ($_POST['return_qs'] ?? ''), $qs);
    $keep = [];
    foreach ($allowedKeys as $key) {
        if (isset($qs[$key]) && is_scalar($qs[$key])) {
            $keep[$key] = (string) $qs[$key];
        }
    }
    return url($page, $keep);
}

/** Render a view file with variables extracted into its scope. */
function render(string $view, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require SRC_PATH . '/views/' . $view;
}

/* ------------------------------------------------------------------ */
/* Flash messages and form state                                      */
/* ------------------------------------------------------------------ */

function flash_add(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_pull(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Remember validation errors and submitted values across a redirect. */
function form_state_set(array $errors, array $old): void
{
    $_SESSION['form_state'] = ['errors' => $errors, 'old' => $old];
}

/** @return array{0: array, 1: array} [errors, old input] */
function form_state_pull(): array
{
    $state = $_SESSION['form_state'] ?? ['errors' => [], 'old' => []];
    unset($_SESSION['form_state']);
    return [$state['errors'], $state['old']];
}

/* ------------------------------------------------------------------ */
/* CSRF protection                                                    */
/* ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent  = (string) ($_POST['_csrf'] ?? '');
    $known = (string) ($_SESSION['csrf'] ?? '');
    if ($known === '' || !hash_equals($known, $sent)) {
        throw new HttpException(419, 'Your session expired or the form was invalid. Go back, reload the page, and try again.');
    }
}

/* ------------------------------------------------------------------ */
/* Database shortcuts                                                 */
/* ------------------------------------------------------------------ */

/**
 * Prepare and execute a statement with typed parameter binding.
 * Parameter names must include the leading colon, e.g. [':id' => 5].
 */
function db_query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    foreach ($params as $name => $value) {
        $type = match (true) {
            is_int($value)  => PDO::PARAM_INT,
            is_null($value) => PDO::PARAM_NULL,
            is_bool($value) => PDO::PARAM_BOOL,
            default         => PDO::PARAM_STR,
        };
        $statement->bindValue($name, $value, $type);
    }
    $statement->execute();
    return $statement;
}

/** Escape LIKE/ILIKE wildcards in user input (use with ESCAPE '\'). */
function like_escape(string $value): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
}

/** SQL fragment joining each applicant (alias "a") to their latest application status as "ls". */
function latest_status_join(): string
{
    return 'LEFT JOIN LATERAL (
                SELECT s.status_name
                  FROM application ap
                  JOIN application_status s ON s.status_id = ap.status_id
                 WHERE ap.applicant_id = a.applicant_id
                 ORDER BY ap.application_date DESC, ap.application_id DESC
                 LIMIT 1
            ) ls ON TRUE';
}

/* ------------------------------------------------------------------ */
/* Validation and formatting                                          */
/* ------------------------------------------------------------------ */

/** True for a real calendar date written as YYYY-MM-DD. */
function is_valid_date(string $value): bool
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
        return false;
    }
    return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
}

function format_date(?string $value, string $format = 'M j, Y'): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    return (new DateTimeImmutable($value))->format($format);
}

function format_datetime(?string $value): string
{
    return format_date($value, 'M j, Y g:i A');
}

/* ------------------------------------------------------------------ */
/* Pagination                                                         */
/* ------------------------------------------------------------------ */

function paginate(int $total, int $current, int $perPage = 10): array
{
    $pages   = max(1, (int) ceil($total / $perPage));
    $current = min(max(1, $current), $pages);
    return [
        'total'    => $total,
        'page'     => $current,
        'pages'    => $pages,
        'per_page' => $perPage,
        'offset'   => ($current - 1) * $perPage,
    ];
}

/** Render the "Showing x-y of z" footer with page links (parameter "p"). */
function render_pagination(string $page, array $params, array $pager): string
{
    $from = $pager['total'] === 0 ? 0 : $pager['offset'] + 1;
    $to   = min($pager['offset'] + $pager['per_page'], $pager['total']);

    $html  = '<div class="flex flex-col gap-2 border-t border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">';
    $html .= '<p class="text-sm text-gray-600">Showing <span class="font-medium">' . $from . '</span>–<span class="font-medium">'
           . $to . '</span> of <span class="font-medium">' . $pager['total'] . '</span></p>';

    if ($pager['pages'] > 1) {
        $link = static function (int $target, string $label, bool $active = false, bool $disabled = false) use ($page, $params): string {
            $base = 'rounded-md px-3 py-1 text-sm ring-1 ring-inset ';
            if ($disabled) {
                return '<span class="' . $base . 'cursor-not-allowed text-gray-300 ring-gray-200">' . e($label) . '</span>';
            }
            $class = $active ? 'bg-indigo-600 text-white ring-indigo-600' : 'text-gray-700 ring-gray-300 hover:bg-gray-50';
            return '<a href="' . e(url($page, $params + ['p' => $target])) . '" class="' . $base . $class . '"'
                 . ($active ? ' aria-current="page"' : '') . '>' . e($label) . '</a>';
        };

        $html .= '<nav class="flex flex-wrap gap-1" aria-label="Pagination">';
        $html .= $link($pager['page'] - 1, 'Previous', false, $pager['page'] <= 1);
        for ($i = max(1, $pager['page'] - 2); $i <= min($pager['pages'], $pager['page'] + 2); $i++) {
            $html .= $link($i, (string) $i, $i === $pager['page']);
        }
        $html .= $link($pager['page'] + 1, 'Next', false, $pager['page'] >= $pager['pages']);
        $html .= '</nav>';
    }

    return $html . '</div>';
}

/* ------------------------------------------------------------------ */
/* UI helpers                                                         */
/* ------------------------------------------------------------------ */

function badge(string $label, string $tone = 'gray'): string
{
    $tones = [
        'gray'   => 'bg-gray-100 text-gray-700 ring-gray-500/20',
        'yellow' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
        'blue'   => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'purple' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'green'  => 'bg-green-50 text-green-700 ring-green-600/20',
        'red'    => 'bg-red-50 text-red-700 ring-red-600/20',
    ];
    return '<span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '
         . ($tones[$tone] ?? $tones['gray']) . '">' . e($label) . '</span>';
}

/** Badge for an application status name (Pending, Under Review, ...). */
function status_badge(?string $name): string
{
    if ($name === null || $name === '') {
        return '<span class="text-xs text-gray-400">No applications</span>';
    }
    $tones = [
        'Pending'      => 'yellow',
        'Under Review' => 'blue',
        'Interview'    => 'purple',
        'Accepted'     => 'green',
        'Rejected'     => 'red',
    ];
    return badge($name, $tones[$name] ?? 'gray');
}

/** Badge for a position status (Open, Closed, On Hold). */
function position_badge(string $status): string
{
    $tones = ['Open' => 'green', 'Closed' => 'red', 'On Hold' => 'yellow'];
    return badge($status, $tones[$status] ?? 'gray');
}

/** Badge for an interview result. */
function result_badge(?string $result): string
{
    $result = $result ?: 'Pending';
    $tones  = ['Pending' => 'yellow', 'Passed' => 'green', 'Failed' => 'red', 'No Show' => 'red', 'Rescheduled' => 'blue'];
    return badge($result, $tones[$result] ?? 'gray');
}

/** onsubmit/onclick attribute value that asks for confirmation (safely escaped). */
function js_confirm(string $message): string
{
    $json = json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    return e('return confirm(' . $json . ');');
}

/** Render a labelled text-like input with an inline error message. */
function form_input(string $name, string $label, string $value, array $errors, string $type = 'text', array $attrs = []): string
{
    $error = $errors[$name] ?? null;
    $class = 'mt-1 block w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 '
           . ($error ? 'border-red-400' : 'border-gray-300');

    $attrHtml = '';
    foreach ($attrs as $key => $val) {
        $attrHtml .= $val === true ? ' ' . e($key) : ' ' . e($key) . '="' . e($val) . '"';
    }

    $html  = '<div><label for="' . e($name) . '" class="block text-sm font-medium text-gray-700">' . e($label) . '</label>';
    $html .= '<input id="' . e($name) . '" name="' . e($name) . '" type="' . e($type) . '" value="' . e($value) . '" class="' . $class . '"' . $attrHtml . '>';
    if ($error) {
        $html .= '<p class="mt-1 text-sm text-red-600">' . e($error) . '</p>';
    }
    return $html . '</div>';
}

/* ------------------------------------------------------------------ */
/* Authentication and roles                                           */
/* ------------------------------------------------------------------ */

/** True when a user is signed in (login_process.php sets $_SESSION['user_id']). */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** Read-only account: may browse every page but cannot create, edit or delete. */
function is_viewer(): bool
{
    return ($_SESSION['role'] ?? '') === 'Viewer';
}

/** Administrator and Staff may create, edit and delete records. */
function can_write(): bool
{
    return is_logged_in() && !is_viewer();
}
