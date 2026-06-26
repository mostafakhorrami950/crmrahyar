<?php
namespace Controllers;

use Core\Auth;
use Core\AuditTrail;
use Core\Database;
use Core\Session;
use Core\View;

class AuditController
{
    /**
     * View all change logs (with filters)
     */
    public function index(): void
    {
        $entityType = $_GET['entity_type'] ?? null;
        $userId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        $limit = 200;

        $logs = AuditTrail::getAll($entityType, $userId, $limit);

        // Get all users for filter dropdown
        $db = Database::getInstance();
        $users = $db->fetchAll("SELECT id, full_name FROM users ORDER BY full_name");

        View::render('audit/index', [
            'title' => 'لاگ تغییرات',
            'logs' => $logs,
            'users' => $users,
            'selectedEntityType' => $entityType,
            'selectedUserId' => $userId,
        ]);
    }

    /**
     * View change history for a specific entity
     */
    public function history(array $params): void
    {
        $entityType = $params['type']; // contact or deal
        $entityId = (int)$params['id'];

        $logs = AuditTrail::getHistory($entityType, $entityId);

        // Get entity name
        $db = Database::getInstance();
        $table = $entityType === 'contact' ? 'contacts' : 'deals';
        $nameField = $entityType === 'contact' ? 'full_name' : 'title';
        $entity = $db->fetch("SELECT {$nameField} FROM {$table} WHERE id = :id", [':id' => $entityId]);
        $entityName = $entity ? $entity->{$nameField} : 'حذف شده';

        View::render('audit/history', [
            'title' => "تاریخچه تغییرات: {$entityName}",
            'logs' => $logs,
            'entityType' => $entityType,
            'entityId' => $entityId,
            'entityName' => $entityName,
        ]);
    }

    /**
     * Rollback to a previous version
     */
    public function rollback(): void
    {
        $logId = (int)($_POST['log_id'] ?? 0);
        $entityType = $_POST['entity_type'] ?? '';

        if (!$logId || !in_array($entityType, ['contact', 'deal'])) {
            Session::setFlash('danger', 'پارامترهای نامعتبر.');
            View::redirect('/audit');
            return;
        }

        if (!Auth::hasPermission('audit.rollback')) {
            Session::setFlash('danger', 'دسترسی لازم را ندارید.');
            View::redirect('/audit');
            return;
        }

        $result = AuditTrail::rollback($entityType, $logId);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('danger', $result['message']);
        }

        $log = Database::getInstance()->fetch(
            "SELECT entity_id FROM change_logs WHERE id = :id",
            [':id' => $logId]
        );
        if ($log) {
            $route = $entityType === 'contact' ? '/contacts/view/' : '/deals/view/';
            View::redirect($route . $log->entity_id);
        } else {
            View::redirect('/audit');
        }
    }
}