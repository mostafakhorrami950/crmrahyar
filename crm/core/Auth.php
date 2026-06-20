<?php
namespace Core;

class Auth
{
    private static $user = null;

    public static function attempt(string $username, string $password): bool
    {
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT u.*, r.name as role_name, r.slug as role_slug 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.username = :username AND u.is_active = 1",
            [':username' => $username]
        );

        if ($user && password_verify($password, $user->password)) {
            Session::set('user_id', $user->id);
            Session::set('user_name', $user->full_name);
            Session::set('role_slug', $user->role_slug);
            
            // Update last login
            $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $user->id]);
            
            // Log activity
            ActivityLog::log('login', 'user', $user->id, 'ورود به سیستم');
            
            return true;
        }

        return false;
    }

    public static function user(): ?object
    {
        if (self::$user === null) {
            $userId = Session::get('user_id');
            if ($userId) {
                $db = Database::getInstance();
                self::$user = $db->fetch(
                    "SELECT u.*, r.name as role_name, r.slug as role_slug 
                     FROM users u 
                     JOIN roles r ON u.role_id = r.id 
                     WHERE u.id = :id",
                    [':id' => $userId]
                );
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function logout(): void
    {
        ActivityLog::log('logout', 'user', self::id(), 'خروج از سیستم');
        Session::destroy();
    }

    public static function hasPermission(string $permission): bool
    {
        $user = self::user();
        if (!$user) return false;

        // Super admin has all permissions
        if ($user->role_slug === 'super_admin') return true;

        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as count FROM role_permissions 
             WHERE role_id = :role_id AND permission = :permission",
            [':role_id' => $user->role_id, ':permission' => $permission]
        );

        return $result && $result->count > 0;
    }

    /**
     * Get the permission scope for the current user.
     * Returns 'own', 'all', or null if no permission.
     */
    public static function getPermissionScope(string $permission): ?string
    {
        $user = self::user();
        if (!$user) return null;

        // Super admin has 'all' scope for everything
        if ($user->role_slug === 'super_admin') return 'all';

        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT scope FROM role_permissions 
             WHERE role_id = :role_id AND permission = :permission",
            [':role_id' => $user->role_id, ':permission' => $permission]
        );

        return $result ? $result->scope : null;
    }

    /**
     * Check if user can access ALL data (scope = 'all').
     * If scope is 'own', returns false.
     * If no permission at all, returns false.
     */
    public static function canAccessAll(string $permission): bool
    {
        return self::getPermissionScope($permission) === 'all';
    }

    /**
     * Build SQL WHERE clause for data filtering based on user's permission scope.
     * Returns a WHERE clause string and binds the user_id parameter.
     * For 'own' scope: filters by owner columns.
     * For 'all' scope: returns "1=1" (no filter).
     * For no permission: returns "1=0" (no access).
     * 
     * @param string $permission The permission slug to check
     * @param array  $ownerColumns Columns that represent ownership (e.g. ['d.assigned_to', 'd.created_by'])
     * @return array ['where' => string, 'params' => array]
     */
    public static function scopeFilter(string $permission, array $ownerColumns): array
    {
        $scope = self::getPermissionScope($permission);
        
        if ($scope === 'all') {
            return ['where' => '1=1', 'params' => []];
        }
        
        if ($scope === 'own') {
            $userId = self::id();
            $conditions = [];
            $params = [];
            foreach ($ownerColumns as $i => $col) {
                $paramKey = ':scope_uid_' . $i;
                $conditions[] = "({$col} = {$paramKey})";
                $params[$paramKey] = $userId;
            }
            return ['where' => '(' . implode(' OR ', $conditions) . ')', 'params' => $params];
        }
        
        return ['where' => '1=0', 'params' => []];
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::setFlash('danger', 'لطفا ابتدا وارد سیستم شوید.');
            header('Location: ' . $GLOBALS['app_config']['url'] . '/login');
            exit;
        }
    }

    public static function isOperator(): bool
    {
        $user = self::user();
        return $user && $user->role_slug === 'operator';
    }

    public static function ownsDeal(int $dealId): bool
    {
        if (!self::isOperator()) return true; // Non-operators can access all
        $db = Database::getInstance();
        $deal = $db->fetch("SELECT assigned_to, created_by FROM deals WHERE id = :id", [':id' => $dealId]);
        $userId = self::id();
        return $deal && ($deal->assigned_to == $userId || $deal->created_by == $userId);
    }

    public static function requirePermission(string $permission): void
    {
        self::requireAuth();
        if (!self::hasPermission($permission)) {
            Session::setFlash('danger', 'شما دسترسی به این بخش را ندارید.');
            $url = $GLOBALS['app_config']['url'] ?? '';
            header('Location: ' . $url . '/dashboard');
            exit;
        }
    }
}