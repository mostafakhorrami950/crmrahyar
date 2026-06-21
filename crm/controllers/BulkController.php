<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\ActivityLog;

class BulkController
{
    /**
     * Bulk delete items from any table
     * POST /bulk/delete
     * Body: entity (contacts|deals|activity_logs), ids[] 
     */
    public function delete(): void
    {
        // Suppress any PHP warnings/notices that would break JSON
        error_reporting(0);
        while (ob_get_level()) ob_end_clean();
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $entity = $_POST['entity'] ?? '';
            $ids = $_POST['ids'] ?? [];
            
            if (empty($entity) || empty($ids)) {
                echo json_encode(['success' => false, 'message' => 'آیتمی انتخاب نشده.']);
                exit;
            }
            
            // Sanitize IDs
            $ids = array_map('intval', $ids);
            $ids = array_values(array_filter($ids, fn($id) => $id > 0));
            
            if (empty($ids)) {
                echo json_encode(['success' => false, 'message' => 'شناسه‌های نامعتبر.']);
                exit;
            }
            
            $db = Database::getInstance();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $tables = [
                'contacts' => ['table' => 'contacts', 'permission' => 'contacts.delete'],
                'deals' => ['table' => 'deals', 'permission' => 'deals.delete'],
                'activity_logs' => ['table' => 'activity_logs', 'permission' => 'deals.edit'],
            ];
            
            if (!isset($tables[$entity])) {
                echo json_encode(['success' => false, 'message' => 'نوع نامعتبر.']);
                exit;
            }
            
            $config = $tables[$entity];
            
            // Check permission
            if (!Auth::hasPermission($config['permission'])) {
                echo json_encode(['success' => false, 'message' => 'دسترسی ندارید.']);
                exit;
            }
            
            $table = $config['table'];
            $count = $db->delete($table, "id IN ({$placeholders})", $ids);
            
            ActivityLog::log('bulk_delete', $entity, 0, count($ids) . " مورد از {$entity} حذف شد");
            
            echo json_encode([
                'success' => true,
                'message' => count($ids) . ' مورد با موفقیت حذف شد.',
                'deleted' => count($ids),
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'خطای سرور: ' . $e->getMessage()]);
        }
        exit;
    }
}