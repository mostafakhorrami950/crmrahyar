<?php
namespace Core;

/**
 * AuditTrail - Track all changes to entities with rollback support
 */
class AuditTrail
{
    /**
     * Log a change to an entity
     */
    public static function log(string $entityType, int $entityId, string $action, ?array $oldData = null, ?array $newData = null): void
    {
        try {
            $db = Database::getInstance();
            $userId = Auth::id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $changes = null;
            $snapshot = null;

            if ($action === 'update' && $oldData && $newData) {
                $changes = self::diff($oldData, $newData);
                // Don't log if nothing actually changed
                if (empty($changes)) return;
                $snapshot = $oldData;
            } elseif ($action === 'create' && $newData) {
                $snapshot = $newData;
            } elseif ($action === 'delete' && $oldData) {
                $snapshot = $oldData;
            }

            $db->insert('change_logs', [
                'user_id'      => $userId ?: null,
                'entity_type'  => $entityType,
                'entity_id'    => $entityId,
                'action'       => $action,
                'changes'      => $changes ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null,
                'snapshot'     => $snapshot ? json_encode($snapshot, JSON_UNESCAPED_UNICODE) : null,
                'ip_address'   => $ip,
            ]);
        } catch (\Exception $e) {
            Logger::error("AuditTrail log failed: " . $e->getMessage());
        }
    }

    /**
     * Compute diff between old and new data arrays
     */
    private static function diff(array $old, array $new): array
    {
        $changes = [];
        // Skip non-data fields
        $skip = ['updated_at', 'created_at', 'password'];

        foreach ($new as $key => $newVal) {
            if (in_array($key, $skip)) continue;
            $oldVal = $old[$key] ?? null;
            if ((string)$oldVal !== (string)$newVal) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }
        return $changes;
    }

    /**
     * Get change history for an entity
     */
    public static function getHistory(string $entityType, int $entityId, int $limit = 50): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT cl.*, u.full_name as user_name 
             FROM change_logs cl 
             LEFT JOIN users u ON cl.user_id = u.id 
             WHERE cl.entity_type = :et AND cl.entity_id = :eid 
             ORDER BY cl.created_at DESC 
             LIMIT :lim",
            [':et' => $entityType, ':eid' => $entityId, ':lim' => $limit]
        );
    }

    /**
     * Get all change logs (with filters)
     */
    public static function getAll(?string $entityType = null, ?int $userId = null, int $limit = 100): array
    {
        $db = Database::getInstance();
        $where = [];
        $params = [];

        if ($entityType) {
            $where[] = "cl.entity_type = :et";
            $params[':et'] = $entityType;
        }
        if ($userId) {
            $where[] = "cl.user_id = :uid";
            $params[':uid'] = $userId;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[':lim'] = $limit;

        return $db->fetchAll(
            "SELECT cl.*, u.full_name as user_name 
             FROM change_logs cl 
             LEFT JOIN users u ON cl.user_id = u.id 
             {$whereSql}
             ORDER BY cl.created_at DESC 
             LIMIT :lim",
            $params
        );
    }

    /**
     * Restore an entity to a previous version (rollback)
     */
    public static function rollback(string $entityType, int $logId): array
    {
        Auth::requireAuth();
        
        // Security: Only admins can rollback
        if (!Auth::isAdmin()) {
            return ['success' => false, 'message' => 'فقط مدیر اصلی می‌تواند تغییرات را بازگردانی کند'];
        }

        $db = Database::getInstance();

        // Get the log entry
        $log = $db->fetch(
            "SELECT * FROM change_logs WHERE id = :id AND entity_type = :et",
            [':id' => $logId, ':et' => $entityType]
        );

        if (!$log) {
            return ['success' => false, 'message' => 'لاگ تغییرات یافت نشد'];
        }

        $snapshot = json_decode($log->snapshot, true);
        if (!$snapshot || empty($snapshot)) {
            return ['success' => false, 'message' => 'نسخه قبلی موجود نیست'];
        }

        $tableMap = [
            'contact' => 'contacts',
            'deal' => 'deals',
            'pipeline' => 'pipelines',
            'user' => 'users',
        ];
        $table = $tableMap[$entityType] ?? null;
        if (!$table) {
            return ['success' => false, 'message' => 'نوع موجودیت پشتیبانی نمی‌شود'];
        }
        $entityId = $log->entity_id;

        // Handle DELETE rollback: re-insert the deleted record
        if ($log->action === 'delete') {
            // Check if record already exists (already restored)
            $existing = $db->fetch("SELECT id FROM {$table} WHERE id = :id", [':id' => $entityId]);
            if ($existing) {
                return ['success' => false, 'message' => 'این رکورد قبلاً بازیابی شده است'];
            }

            // Remove auto-increment fields
            unset($snapshot['updated_at']);
            
            // Re-insert the deleted record with original ID
            $columns = array_keys($snapshot);
            $placeholders = [];
            $params = [];
            foreach ($snapshot as $key => $val) {
                $ph = ":{$key}";
                $placeholders[] = $ph;
                $params[$ph] = $val;
            }

            $db->query(
                "INSERT INTO {$table} (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")",
                $params
            );

            // Log the restore action
            self::log($entityType, $entityId, 'restore', null, $snapshot);

            return ['success' => true, 'message' => 'رکورد حذف شده با موفقیت بازیابی شد'];
        }

        // Handle UPDATE rollback: restore to previous version
        $currentData = $db->fetch("SELECT * FROM {$table} WHERE id = :id", [':id' => $entityId]);
        if (!$currentData) {
            return ['success' => false, 'message' => 'رکورد یافت نشد. احتمالاً حذف شده است.'];
        }
        $currentArr = (array)$currentData;

        // Remove non-updatable fields from snapshot
        unset($snapshot['id'], $snapshot['created_at'], $snapshot['updated_at']);

        // Build update query
        $sets = [];
        $params = [':id' => $entityId];
        foreach ($snapshot as $key => $val) {
            if ($key === 'id' || $key === 'created_at') continue;
            $sets[] = "`{$key}` = :{$key}";
            $params[":{$key}"] = $val;
        }

        if (empty($sets)) {
            return ['success' => false, 'message' => 'داده‌ای برای بازگردانی موجود نیست'];
        }

        $db->query(
            "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE id = :id",
            $params
        );

        // Log the rollback action
        self::log($entityType, $entityId, 'update', $currentArr, $snapshot);

        return ['success' => true, 'message' => 'بازگردانی با موفقیت انجام شد'];
    }

    /**
     * Get human-readable field name in Persian
     */
    public static function getFieldLabel(string $field): string
    {
        $labels = [
            'full_name' => 'نام کامل',
            'phone' => 'تلفن',
            'email' => 'ایمیل',
            'national_code' => 'کد ملی',
            'passport_number' => 'شماره پاسپورت',
            'address' => 'آدرس',
            'company' => 'شرکت',
            'notes' => 'یادداشت‌ها',
            'source' => 'منبع آشنایی',
            'tags' => 'برچسب‌ها',
            'title' => 'عنوان',
            'description' => 'توضیحات',
            'amount' => 'مبلغ',
            'currency' => 'واحد پول',
            'pipeline_id' => 'پایپ لاین',
            'stage_id' => 'مرحله',
            'contact_id' => 'مخاطب',
            'assigned_to' => 'مسئول',
            'probability' => 'احتمال موفقیت',
            'expected_close_date' => 'تاریخ بسته شدن',
            'lost_reason' => 'دلیل باخت',
            'is_lost' => 'وضعیت باخت',
            'is_won' => 'وضعیت برد',
            'category' => 'دسته‌بندی',
            'phone_number' => 'شماره تلفن',
            'name' => 'نام',
            'is_active' => 'وضعیت فعال',
        ];
        return $labels[$field] ?? $field;
    }

    /**
     * Get action label in Persian
     */
    public static function getActionLabel(string $action): string
    {
        $labels = [
            'create' => 'ایجاد',
            'update' => 'ویرایش',
            'delete' => 'حذف',
            'restore' => 'بازیابی',
        ];
        return $labels[$action] ?? $action;
    }
}