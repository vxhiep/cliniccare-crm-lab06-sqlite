<?php
class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    public function login(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        render('auth/login', ['title' => 'Login', 'errors' => [], 'old' => []]);
    }

    public function handleLogin(): void
    {
        verify_csrf();
        $result = $this->auth->attemptLogin($_POST);
        if (!$result['success']) {
            render('auth/login', [
                'title' => 'Login',
                'errors' => $result['errors'],
                'old' => $result['old'] ?? [],
            ]);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_name'] = $result['user']['name'];
        $_SESSION['user_email'] = $result['user']['email'];
        $_SESSION['user_role'] = $result['user']['role'];
        $_SESSION['last_activity'] = time();
        flash('success', 'Đăng nhập thành công.');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
        }
        session_destroy();
        session_start();
        flash('success', 'Bạn đã đăng xuất an toàn.');
        redirect('/login');
    }
}
