<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class AuthController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 minutes

    public function loginForm(): void
    {
        if (Auth::check()) {
            View::redirect('/dashboard');
        }
        View::render('auth/login', ['title' => 'ورود به سیستم']);
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (empty($username) || empty($password)) {
            Session::setFlash('danger', 'لطفا نام کاربری و رمز عبور را وارد کنید.');
            View::redirect('/login');
        }

        // Rate limiting by IP
        $db = Database::getInstance();
        $recentAttempts = $db->fetch(
            "SELECT COUNT(*) as cnt FROM activity_logs 
             WHERE action = 'login_failed' AND ip_address = :ip 
             AND created_at > DATE_SUB(NOW(), INTERVAL :seconds SECOND)",
            [':ip' => $ip, ':seconds' => self::LOCKOUT_SECONDS]
        );
        
        if ($recentAttempts && $recentAttempts->cnt >= self::MAX_LOGIN_ATTEMPTS) {
            ActivityLog::log('login_blocked', 'user', 0, "لاگین مسدود شد برای IP: {$ip}");
            Session::setFlash('danger', 'تعداد تلاش‌های ناموفق زیاد بود. لطفاً ۱۵ دقیقه دیگر تلاش کنید.');
            View::redirect('/login');
        }

        if (Auth::attempt($username, $password)) {
            Session::setFlash('success', 'خوش آمدید!');
            View::redirect('/dashboard');
        } else {
            // Log failed attempt
            ActivityLog::log('login_failed', 'user', 0, "لاگین ناموفق: {$username} از IP: {$ip}");
            Session::setFlash('danger', 'نام کاربری یا رمز عبور اشتباه است.');
            View::redirect('/login');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        Session::setFlash('success', 'با موفقیت خارج شدید.');
        View::redirect('/login');
    }
}