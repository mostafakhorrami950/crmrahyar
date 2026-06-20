<?php
namespace Core;

class Notification
{
    public static function create(int $userId, string $type, string $title, string $message = '', string $link = '', string $entityType = '', int $entityId = 0, int $fromUserId = 0): int
    {
        $db = Database::getInstance();
        return $db->insert('notifications', [
            'user_id' => $userId,
            'from_user_id' => $fromUserId ?: Auth::id(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }

    public static function getUnread(int $userId, int $limit = 20): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT n.*, u.full_name as from_user_name 
             FROM notifications n 
             LEFT JOIN users u ON n.from_user_id = u.id 
             WHERE n.user_id = :uid AND n.is_read = 0 
             ORDER BY n.created_at DESC LIMIT {$limit}",
            [':uid' => $userId]
        );
    }

    public static function getUnreadCount(int $userId): int
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as cnt FROM notifications WHERE user_id = :uid AND is_read = 0",
            [':uid' => $userId]
        );
        return (int)($result->cnt ?? 0);
    }

    public static function markRead(int $id, int $userId): void
    {
        $db = Database::getInstance();
        $db->update('notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')], 'id = :id AND user_id = :uid', [':id' => $id, ':uid' => $userId]);
    }

    public static function markAllRead(int $userId): void
    {
        $db = Database::getInstance();
        $db->query("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = :uid AND is_read = 0", [':uid' => $userId]);
    }

    public static function getAll(int $userId, int $page = 1, int $perPage = 20): array
    {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;
        $items = $db->fetchAll(
            "SELECT n.*, u.full_name as from_user_name 
             FROM notifications n 
             LEFT JOIN users u ON n.from_user_id = u.id 
             WHERE n.user_id = :uid 
             ORDER BY n.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            [':uid' => $userId]
        );
        $total = $db->fetch("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = :uid", [':uid' => $userId]);
        return ['items' => $items, 'total' => (int)($total->cnt ?? 0), 'page' => $page, 'perPage' => $perPage];
    }
}