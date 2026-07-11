<?php
namespace Shared\Core;

class Auth
{
    public static function requireAuth(): void
    {
        if (!self::check()) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید.']);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?object
    {
        $userId = self::id();
        if (!$userId) return null;
        static $user = null;
        if ($user !== null) return $user;
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT u.*, r.name as role_name, r.slug as role_slug FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = :id",
            [':id' => $userId]
        );
        return $user;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && ($user->role_slug === 'super_admin' || $user->role_slug === 'admin');
    }

    public static function name(): string
    {
        return $_SESSION['user_name'] ?? '';
    }
}