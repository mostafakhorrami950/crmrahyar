<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Core\Validator;

class AuthController
{
    private Database $db;
    private Config $config;

    public function __construct()
    {
        $c = \Shared\Core\Container::getInstance();
        $this->db = $c->make(Database::class);
        $this->config = $c->make(Config::class);
    }

    public function loginForm(array $params = []): void
    {
        if ($this->isLoggedIn()) { header('Location: /dashboard'); exit; }
        $meta = ['title' => 'ورود', 'description' => 'ورود به حساب کاربری'];
        $this->render('auth/login', ['meta' => $meta, 'error' => $_SESSION['auth_error'] ?? null]);
        unset($_SESSION['auth_error']);
    }

    public function login(array $params = []): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['auth_error'] = 'نام کاربری و رمز عبور الزامی است.';
            header('Location: /login'); exit;
        }

        $user = $this->db->fetch(
            "SELECT u.*, r.name as role_name, r.slug as role_slug FROM users u JOIN roles r ON u.role_id = r.id WHERE (u.username = :u OR u.email = :u) AND u.is_active = 1",
            [':u' => $username]
        );

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->full_name;
            $_SESSION['role_slug'] = $user->role_slug;
            header('Location: /dashboard'); exit;
        }

        $_SESSION['auth_error'] = 'نام کاربری یا رمز عبور اشتباه است.';
        header('Location: /login'); exit;
    }

    public function registerForm(array $params = []): void
    {
        if ($this->isLoggedIn()) { header('Location: /dashboard'); exit; }
        $meta = ['title' => 'ثبت‌نام', 'description' => 'ایجاد حساب کاربری'];
        $this->render('auth/register', ['meta' => $meta, 'error' => $_SESSION['auth_error'] ?? null]);
        unset($_SESSION['auth_error']);
    }

    public function register(array $params = []): void
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $validator = new Validator($_POST);
        $validator->required('full_name', 'email', 'password');
        $validator->email('email');

        if ($validator->fails()) {
            $_SESSION['auth_error'] = $validator->firstError();
            header('Location: /register'); exit;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['auth_error'] = 'رمز عبور و تکرار آن مطابقت ندارند.';
            header('Location: /register'); exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['auth_error'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
            header('Location: /register'); exit;
        }

        // Check unique email
        $existing = $this->db->fetch("SELECT id FROM users WHERE email = :e", [':e' => $email]);
        if ($existing) {
            $_SESSION['auth_error'] = 'این ایمیل قبلاً ثبت شده است.';
            header('Location: /register'); exit;
        }

        // Get default role
        $role = $this->db->fetch("SELECT id FROM roles WHERE slug = 'user' LIMIT 1");
        if (!$role) {
            $role = $this->db->fetch("SELECT id FROM roles ORDER BY id ASC LIMIT 1");
        }

        $userId = $this->db->insert('users', [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'username' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $role->id ?? 1,
            'is_active' => 1,
        ]);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['role_slug'] = 'user';
        header('Location: /dashboard'); exit;
    }

    public function logout(array $params = []): void
    {
        session_destroy();
        header('Location: /'); exit;
    }

    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) { require $viewPath; } else { echo "View not found: {$view}"; }
    }
}