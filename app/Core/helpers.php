<?php
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function render(string $view, array $data = [], string $layout = 'layouts/main'): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require APP_PATH . '/Views/' . $view . '.php';
    $content = ob_get_clean();
    require APP_PATH . '/Views/' . $layout . '.php';
    exit;
}

function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require APP_PATH . '/Views/partials/' . $name . '.php';
}

function flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(?string $key = null): mixed
{
    if ($key === null) {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $message;
}

function old(string $key, array $old = [], string $default = ''): string
{
    return (string)($old[$key] ?? $default);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'staff',
    ];
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('/login');
    }
}

function check_session_timeout(int $timeout): void
{
    if (!is_logged_in()) {
        return;
    }

    $lastActivity = (int)($_SESSION['last_activity'] ?? 0);
    if ($lastActivity > 0 && time() - $lastActivity > $timeout) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
        }
        session_destroy();
        session_start();
        flash('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        redirect('/login');
    }

    $_SESSION['last_activity'] = time();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        render('errors/500', [
            'title' => 'Invalid Request',
            'message' => 'Yêu cầu không hợp lệ hoặc đã hết phiên bảo mật. Vui lòng thử lại.',
        ]);
    }
}

function selected(string $current, string $expected): string
{
    return $current === $expected ? 'selected' : '';
}

function field_error(array $errors, string $key): string
{
    if (empty($errors[$key])) {
        return '';
    }
    return '<div class="field-error">' . e($errors[$key]) . '</div>';
}

function app_log(Throwable|string $error): void
{
    $message = $error instanceof Throwable
        ? sprintf('[%s] %s in %s:%s', get_class($error), $error->getMessage(), $error->getFile(), $error->getLine())
        : $error;

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents(ROOT_PATH . '/storage/logs/app.log', $line, FILE_APPEND);
}
