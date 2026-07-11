<?php
namespace Site\Controllers;

use Shared\Core\Database;
use Shared\Core\Config;
use Shared\Services\IranPayamakSMS;

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
        $step = $_SESSION['login_step'] ?? 'phone';
        $this->render('auth/login', ['meta' => $meta, 'error' => $_SESSION['auth_error'] ?? null, 'step' => $step]);
        unset($_SESSION['auth_error']);
    }

    public function login(array $params = []): void
    {
        $step = $_POST['step'] ?? 'phone';

        if ($step === 'send_code') {
            $this->loginSendCode();
        } elseif ($step === 'verify') {
            $this->loginVerify();
        } else {
            $_SESSION['auth_error'] = 'درخواست نامعتبر.';
            header('Location: /login'); exit;
        }
    }

    private function loginSendCode(): void
    {
        $phone = trim($_POST['phone'] ?? '');

        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            $_SESSION['auth_error'] = 'شماره موبایل نامعتبر است.';
            header('Location: /login'); exit;
            return;
        }

        $user = $this->db->fetch("SELECT id, full_name FROM users WHERE phone = :p AND is_active = 1", [':p' => $phone]);
        if (!$user) {
            $_SESSION['auth_error'] = 'کاربری با این شماره یافت نشد.';
            header('Location: /login'); exit;
            return;
        }

        $code = IranPayamakSMS::generateCode(5);
        $sms = new IranPayamakSMS();
        $result = $sms->sendVerification($phone, $code);

        if (!$result['success']) {
            $_SESSION['auth_error'] = 'خطا در ارسال پیامک: ' . ($result['message'] ?? '');
            header('Location: /login'); exit;
            return;
        }

        $_SESSION['login_phone'] = $phone;
        $_SESSION['login_code'] = $code;
        $_SESSION['login_code_time'] = time();
        $_SESSION['login_step'] = 'verify';

        header('Location: /login'); exit;
    }

    private function loginVerify(): void
    {
        $inputCode = trim($_POST['code'] ?? '');

        if (empty($inputCode) || !isset($_SESSION['login_code'])) {
            $_SESSION['auth_error'] = 'کد تایید را وارد کنید.';
            $_SESSION['login_step'] = 'phone';
            header('Location: /login'); exit;
            return;
        }

        if (time() - ($_SESSION['login_code_time'] ?? 0) > 300) {
            $_SESSION['auth_error'] = 'کد تایید منقضی شده.';
            $_SESSION['login_step'] = 'phone';
            header('Location: /login'); exit;
            return;
        }

        if ($inputCode !== $_SESSION['login_code']) {
            $_SESSION['auth_error'] = 'کد تایید اشتباه است.';
            header('Location: /login'); exit;
            return;
        }

        $phone = $_SESSION['login_phone'];
        $user = $this->db->fetch(
            "SELECT u.id, u.full_name, r.slug as role_slug FROM users u JOIN roles r ON u.role_id = r.id WHERE u.phone = :p AND u.is_active = 1",
            [':p' => $phone]
        );

        unset($_SESSION['login_phone'], $_SESSION['login_code'], $_SESSION['login_code_time'], $_SESSION['login_step']);

        if ($user) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->full_name;
            $_SESSION['role_slug'] = $user->role_slug;
            header('Location: /dashboard'); exit;
        }

        $_SESSION['auth_error'] = 'خطا در ورود.';
        header('Location: /login'); exit;
    }

    public function registerForm(array $params = []): void
    {
        if ($this->isLoggedIn()) { header('Location: /dashboard'); exit; }
        $meta = ['title' => 'ثبت‌نام', 'description' => 'ایجاد حساب کاربری'];
        $step = $_SESSION['register_step'] ?? 'form';
        $this->render('auth/register', ['meta' => $meta, 'error' => $_SESSION['auth_error'] ?? null, 'step' => $step]);
        unset($_SESSION['auth_error']);
    }

    public function register(array $params = []): void
    {
        $step = $_POST['step'] ?? 'form';

        if ($step === 'send_code') {
            $this->registerSendCode();
        } elseif ($step === 'verify') {
            $this->registerVerify();
        } else {
            $_SESSION['auth_error'] = 'درخواست نامعتبر.';
            header('Location: /register'); exit;
        }
    }

    private function registerSendCode(): void
    {
        $phone = trim($_POST['phone'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');

        if (empty($phone) || empty($fullName)) {
            $_SESSION['auth_error'] = 'نام و شماره موبایل الزامی است.';
            header('Location: /register'); exit;
            return;
        }

        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            $_SESSION['auth_error'] = 'شماره موبایل نامعتبر است. (مثال: 09123456789)';
            header('Location: /register'); exit;
            return;
        }

        $existing = $this->db->fetch("SELECT id FROM users WHERE phone = :p", [':p' => $phone]);
        if ($existing) {
            $_SESSION['auth_error'] = 'این شماره قبلاً ثبت شده. لطفاً وارد شوید.';
            header('Location: /login'); exit;
            return;
        }

        $code = IranPayamakSMS::generateCode(5);
        $sms = new IranPayamakSMS();
        $result = $sms->sendVerification($phone, $code);

        if (!$result['success']) {
            $_SESSION['auth_error'] = 'خطا در ارسال پیامک: ' . ($result['message'] ?? '');
            header('Location: /register'); exit;
            return;
        }

        $_SESSION['register_phone'] = $phone;
        $_SESSION['register_name'] = $fullName;
        $_SESSION['register_code'] = $code;
        $_SESSION['register_code_time'] = time();
        $_SESSION['register_step'] = 'verify';

        header('Location: /register'); exit;
    }

    private function registerVerify(): void
    {
        $inputCode = trim($_POST['code'] ?? '');

        if (empty($inputCode) || !isset($_SESSION['register_code'])) {
            $_SESSION['auth_error'] = 'کد تایید را وارد کنید.';
            header('Location: /register'); exit;
            return;
        }

        if (time() - ($_SESSION['register_code_time'] ?? 0) > 300) {
            $_SESSION['auth_error'] = 'کد تایید منقضی شده.';
            $_SESSION['register_step'] = 'form';
            header('Location: /register'); exit;
            return;
        }

        if ($inputCode !== $_SESSION['register_code']) {
            $_SESSION['auth_error'] = 'کد تایید اشتباه است.';
            header('Location: /register'); exit;
            return;
        }

        $phone = $_SESSION['register_phone'];
        $fullName = $_SESSION['register_name'];

        // Generate random password (user logs in via SMS only)
        $randomPass = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $role = $this->db->fetch("SELECT id FROM roles WHERE slug = 'user' LIMIT 1");
        if (!$role) {
            $role = $this->db->fetch("SELECT id FROM roles ORDER BY id ASC LIMIT 1");
        }

        $userId = $this->db->insert('users', [
            'full_name' => $fullName,
            'phone' => $phone,
            'username' => $phone,
            'email' => $phone . '@temp.local',
            'password' => $randomPass,
            'role_id' => $role->id ?? 1,
            'is_active' => 1,
        ]);

        unset($_SESSION['register_phone'], $_SESSION['register_name']);
        unset($_SESSION['register_code'], $_SESSION['register_code_time'], $_SESSION['register_step']);

        $user = $this->db->fetch("SELECT r.slug as role_slug FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id", [':id' => $userId]);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['role_slug'] = $user->role_slug ?? 'user';

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