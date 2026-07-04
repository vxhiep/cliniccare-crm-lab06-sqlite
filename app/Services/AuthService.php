<?php
class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    public function attemptLogin(array $input): array
    {
        $email = trim($input['email'] ?? '');
        $password = (string)($input['password'] ?? '');
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if ($password === '') {
            $errors['password'] = 'Mật khẩu không được để trống.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'old' => ['email' => $email]];
        }

        $user = $this->users->findActiveByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'errors' => ['general' => 'Email hoặc mật khẩu không đúng.'],
                'old' => ['email' => $email],
            ];
        }

        return ['success' => true, 'user' => $user, 'errors' => []];
    }
}
