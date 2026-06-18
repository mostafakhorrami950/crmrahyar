<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class ContactController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $search = $_GET['search'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (c.full_name LIKE :search OR c.phone LIKE :search2 OR c.email LIKE :search3)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
        }

        $contacts = $db->fetchAll(
            "SELECT c.*, u.full_name as created_by_name,
                    (SELECT COUNT(*) FROM deals WHERE contact_id = c.id) as deals_count,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id AND is_won = 1) as total_purchases,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id) as total_deals_amount
             FROM contacts c 
             LEFT JOIN users u ON c.created_by = u.id 
             {$where}
             ORDER BY c.created_at DESC",
            $params
        );

        View::render('contacts/index', ['title' => 'مدیریت مخاطبان', 'contacts' => $contacts, 'search' => $search]);
    }

    public function view(array $params): void
    {
        $db = Database::getInstance();
        $contact = $db->fetch(
            "SELECT c.*, u.full_name as created_by_name,
                    (SELECT COUNT(*) FROM deals WHERE contact_id = c.id) as deals_count,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id AND is_won = 1) as total_purchases,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id) as total_deals_amount
             FROM contacts c 
             LEFT JOIN users u ON c.created_by = u.id 
             WHERE c.id = :id",
            [':id' => $params['id']]
        );

        if (!$contact) {
            Session::setFlash('danger', 'مخاطب مورد نظر یافت نشد.');
            View::redirect('/contacts');
        }

        // Get all deals for this contact
        $deals = $db->fetchAll(
            "SELECT d.*, s.name as stage_name, s.color as stage_color,
                    p.name as pipeline_name, u.full_name as assigned_name
             FROM deals d
             JOIN stages s ON d.stage_id = s.id
             JOIN pipelines p ON d.pipeline_id = p.id
             LEFT JOIN users u ON d.assigned_to = u.id
             WHERE d.contact_id = :id
             ORDER BY d.created_at DESC",
            [':id' => $params['id']]
        );

        // Get payment history for this contact's deals
        $payments = $db->fetchAll(
            "SELECT p.*, d.title as deal_title
             FROM payments p
             JOIN deals d ON p.deal_id = d.id
             WHERE d.contact_id = :id
             ORDER BY p.created_at DESC",
            [':id' => $params['id']]
        );

        // Get recent activities for this contact's deals
        $activities = $db->fetchAll(
            "SELECT da.*, d.title as deal_title, u2.full_name as user_name
             FROM deal_activities da
             JOIN deals d ON da.deal_id = d.id
             LEFT JOIN users u2 ON da.user_id = u2.id
             WHERE d.contact_id = :id
             ORDER BY da.created_at DESC
             LIMIT 20",
            [':id' => $params['id']]
        );

        View::render('contacts/view', [
            'title' => "مخاطب: {$contact->full_name}",
            'contact' => $contact,
            'deals' => $deals,
            'payments' => $payments,
            'activities' => $activities,
        ]);
    }

    public function create(): void
    {
        View::render('contacts/create', ['title' => 'ایجاد مخاطب جدید']);
    }

    public function store(): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $companyPhone = trim($_POST['company_phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nationalCode = trim($_POST['national_code'] ?? '');
        $passportNumber = trim($_POST['passport_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

        if (empty($fullName)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'لطفا نام مخاطب را وارد کنید.']);
                exit;
            }
            Session::setFlash('danger', 'لطفا نام مخاطب را وارد کنید.');
            View::redirect('/contacts/create');
        }

        $db = Database::getInstance();

        // Check duplicate phone
        if (!empty($phone)) {
            $existing = $db->fetch("SELECT id, full_name FROM contacts WHERE phone = :phone", [':phone' => $phone]);
            if ($existing) {
                if ($isAjax) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "❌ این شماره قبلاً برای «{$existing->full_name}» ثبت شده است.",
                        'duplicate' => true,
                        'existing_id' => $existing->id
                    ]);
                    exit;
                }
                Session::setFlash('danger', "این شماره قبلاً برای «{$existing->full_name}» ثبت شده است.");
                View::redirect('/contacts/create');
            }
        }

        // If no category specified, assign default category
        if (!$categoryId) {
            $defaultCat = $db->fetch("SELECT id FROM contact_categories WHERE is_default = 1");
            $categoryId = $defaultCat ? (int)$defaultCat->id : null;
        }

        $contactId = $db->insert('contacts', [
            'full_name' => $fullName,
            'phone' => $phone,
            'company_phone' => $companyPhone,
            'email' => $email,
            'national_code' => $nationalCode,
            'passport_number' => $passportNumber,
            'address' => $address,
            'company' => $company,
            'notes' => $notes,
            'source' => $source,
            'tags' => $tags,
            'category_id' => $categoryId,
            'created_by' => Auth::id(),
        ]);

        ActivityLog::log('create_contact', 'contact', $contactId, "مخاطب {$fullName} ایجاد شد");

        if ($isAjax) {
            $newContact = $db->fetch("SELECT id, full_name, phone FROM contacts WHERE id = :id", [':id' => $contactId]);
            echo json_encode([
                'success' => true, 
                'message' => 'مخاطب با موفقیت ایجاد شد.',
                'contact' => $newContact
            ]);
            exit;
        }
        Session::setFlash('success', 'مخاطب با موفقیت ایجاد شد.');
        View::redirect('/contacts/view/' . $contactId);
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $contact = $db->fetch("SELECT * FROM contacts WHERE id = :id", [':id' => $params['id']]);
        if (!$contact) {
            Session::setFlash('danger', 'مخاطب مورد نظر یافت نشد.');
            View::redirect('/contacts');
        }
        View::render('contacts/edit', ['title' => 'ویرایش مخاطب', 'contact' => $contact]);
    }

    public function update(array $params): void
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $companyPhone = trim($_POST['company_phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $nationalCode = trim($_POST['national_code'] ?? '');
        $passportNumber = trim($_POST['passport_number'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

        $db = Database::getInstance();
        
        // Check duplicate phone but exclude current contact
        if (!empty($phone)) {
            $existing = $db->fetch("SELECT id, full_name FROM contacts WHERE phone = :phone AND id != :id", [':phone' => $phone, ':id' => $params['id']]);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => "این شماره قبلاً برای «{$existing->full_name}» ثبت شده است."]);
                exit;
            }
        }

        $db->update('contacts', [
            'full_name' => $fullName,
            'phone' => $phone,
            'company_phone' => $companyPhone,
            'email' => $email,
            'national_code' => $nationalCode,
            'passport_number' => $passportNumber,
            'address' => $address,
            'company' => $company,
            'notes' => $notes,
            'source' => $source,
            'tags' => $tags,
            'category_id' => $categoryId,
        ], 'id = :id', [':id' => $params['id']]);

        ActivityLog::log('update_contact', 'contact', $params['id'], "مخاطب {$fullName} ویرایش شد");
        Session::setFlash('success', 'مخاطب با موفقیت ویرایش شد.');
        View::redirect('/contacts/view/' . $params['id']);
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $contact = $db->fetch("SELECT full_name FROM contacts WHERE id = :id", [':id' => $params['id']]);
        if ($contact) {
            $db->delete('contacts', 'id = :id', [':id' => $params['id']]);
            ActivityLog::log('delete_contact', 'contact', $params['id'], "مخاطب {$contact->full_name} حذف شد");
            Session::setFlash('success', 'مخاطب با موفقیت حذف شد.');
        }
        View::redirect('/contacts');
    }
}