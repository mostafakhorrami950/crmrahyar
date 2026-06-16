<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class CustomFieldController
{
    public function index(): void
    {
        Auth::requirePermission('settings.manage');
        $db = Database::getInstance();
        $entityType = $_GET['entity'] ?? 'deals';
        
        $fields = $db->fetchAll(
            "SELECT * FROM custom_fields WHERE entity_type = :entity ORDER BY order_index, id",
            [':entity' => $entityType]
        );
        
        View::render('settings/custom_fields', [
            'title' => 'فیلدهای اختصاصی',
            'fields' => $fields,
            'entityType' => $entityType,
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('settings.manage');
        $entityType = trim($_POST['entity_type'] ?? '');
        $fieldLabel = trim($_POST['field_label'] ?? '');
        $fieldType = trim($_POST['field_type'] ?? 'text');
        $fieldOptions = trim($_POST['field_options'] ?? '');
        $isRequired = isset($_POST['is_required']) ? 1 : 0;

        if (empty($entityType) || empty($fieldLabel)) {
            echo json_encode(['success' => false, 'message' => 'عنوان فیلد الزامی است']);
            exit;
        }

        $fieldName = 'custom_' . uniqid();
        $db = Database::getInstance();
        $db->insert('custom_fields', [
            'entity_type' => $entityType,
            'field_name' => $fieldName,
            'field_label' => $fieldLabel,
            'field_type' => $fieldType,
            'field_options' => $fieldOptions,
            'is_required' => $isRequired,
        ]);

        ActivityLog::log('custom_field_created', 'setting', 0, "فیلد اختصاصی {$fieldLabel} برای {$entityType} ایجاد شد");
        echo json_encode(['success' => true, 'message' => 'فیلد اختصاصی با موفقیت ایجاد شد.']);
        exit;
    }

    public function update(array $params): void
    {
        Auth::requirePermission('settings.manage');
        $fieldLabel = trim($_POST['field_label'] ?? '');
        $fieldType = trim($_POST['field_type'] ?? 'text');
        $fieldOptions = trim($_POST['field_options'] ?? '');
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $db = Database::getInstance();
        $db->update('custom_fields', [
            'field_label' => $fieldLabel,
            'field_type' => $fieldType,
            'field_options' => $fieldOptions,
            'is_required' => $isRequired,
            'is_active' => $isActive,
        ], 'id = :id', [':id' => $params['id']]);

        echo json_encode(['success' => true, 'message' => 'فیلد اختصاصی بروزرسانی شد.']);
        exit;
    }

    public function delete(array $params): void
    {
        Auth::requirePermission('settings.manage');
        $db = Database::getInstance();
        $field = $db->fetch("SELECT * FROM custom_fields WHERE id = :id", [':id' => $params['id']]);
        if ($field) {
            $db->delete('custom_fields', 'id = :id', [':id' => $params['id']]);
            $db->delete('custom_field_values', 'field_id = :id', [':id' => $params['id']]);
        }
        echo json_encode(['success' => true, 'message' => 'فیلد اختصاصی حذف شد.']);
        exit;
    }
}