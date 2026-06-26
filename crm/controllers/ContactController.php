<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;
use Core\AuditTrail;

class ContactController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $search = $_GET['search'] ?? '';
        $categoryId = $_GET['category_id'] ?? '';
        $source = $_GET['source'] ?? '';
        $hasPhone = $_GET['has_phone'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $sortBy = $_GET['sort'] ?? 'created_at';
        $sortDir = $_GET['dir'] ?? 'DESC';
        $perPage = 50;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        
        $where = "WHERE 1=1";
        $params = [];
        
        // Scope-based filtering
        $scope = Auth::scopeFilter('contacts.view', ['c.created_by']);
        if ($scope['where'] !== '1=1') {
            $where .= " AND " . $scope['where'];
            $params = array_merge($params, $scope['params']);
        }
        
        if ($search) {
            $where .= " AND (c.full_name LIKE :search OR c.phone LIKE :search2 OR c.email LIKE :search3 OR c.company LIKE :search4 OR c.national_code LIKE :search5 OR c.company_phone LIKE :search6)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
            $params[':search4'] = "%{$search}%";
            $params[':search5'] = "%{$search}%";
            $params[':search6'] = "%{$search}%";
        }
        
        if ($categoryId !== '') {
            if ($categoryId === '0') {
                $where .= " AND c.category_id IS NULL";
            } else {
                $where .= " AND c.category_id = :category_id";
                $params[':category_id'] = (int)$categoryId;
            }
        }
        
        if ($source) {
            $where .= " AND c.source LIKE :source";
            $params[':source'] = "%{$source}%";
        }
        
        if ($hasPhone === '1') {
            $where .= " AND c.phone IS NOT NULL AND c.phone != ''";
        } elseif ($hasPhone === '0') {
            $where .= " AND (c.phone IS NULL OR c.phone = '')";
        }
        
        if ($dateFrom) {
            $where .= " AND DATE(c.created_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND DATE(c.created_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        
        // Count total for pagination
        $totalResult = $db->fetch("SELECT COUNT(*) as total FROM contacts c {$where}", $params);
        $total = $totalResult ? $totalResult->total : 0;
        $totalPages = max(1, ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        // Allowed sort columns
        $allowedSorts = ['created_at', 'full_name', 'phone', 'company'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $contacts = $db->fetchAll(
            "SELECT c.*, u.full_name as created_by_name,
                    cc.name as category_name, cc.color as category_color,
                    (SELECT COUNT(*) FROM deals WHERE contact_id = c.id) as deals_count,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id AND is_won = 1) as total_purchases,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id) as total_deals_amount
             FROM contacts c 
             LEFT JOIN users u ON c.created_by = u.id 
             LEFT JOIN contact_categories cc ON c.category_id = cc.id
             {$where}
             ORDER BY c.{$sortBy} {$sortDir}
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Load categories for filter dropdown
        $categories = $db->fetchAll("SELECT id, name, color FROM contact_categories ORDER BY name") ?: [];

        // Build query string for pagination links
        $queryString = $_GET;
        unset($queryString['page']);
        $baseQs = http_build_query($queryString);

        View::render('contacts/index', [
            'title' => 'مدیریت مخاطبان',
            'contacts' => $contacts,
            'search' => $search,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'selectedSource' => $source,
            'selectedHasPhone' => $hasPhone,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'perPage' => $perPage,
            'baseQs' => $baseQs,
        ]);
    }

    public function view(array $params): void
    {
        $db = Database::getInstance();
        $contact = $db->fetch(
            "SELECT c.*, u.full_name as created_by_name,
                    cc.name as category_name, cc.color as category_color,
                    (SELECT COUNT(*) FROM deals WHERE contact_id = c.id) as deals_count,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id AND is_won = 1) as total_purchases,
                    (SELECT SUM(amount) FROM deals WHERE contact_id = c.id) as total_deals_amount
             FROM contacts c 
             LEFT JOIN users u ON c.created_by = u.id 
             LEFT JOIN contact_categories cc ON c.category_id = cc.id
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

        $newData = [
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
        ];
        $contactId = $db->insert('contacts', $newData);

        ActivityLog::log('create_contact', 'contact', $contactId, "مخاطب {$fullName} ایجاد شد");
        AuditTrail::log('contact', $contactId, 'create', null, $newData);

        // Fire automation trigger: new_contact
        ob_start();
        \Controllers\AutomationController::execute('new_contact', 'contact', $contactId, [
            'contact_id' => $contactId,
            'contact_name' => $fullName,
            'contact_phone' => $phone,
            'contact_email' => $email ?? '',
        ]);
        ob_end_clean();

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

        // Get old data before update for audit trail
        $oldData = (array)$db->fetch("SELECT * FROM contacts WHERE id = :id", [':id' => $params['id']]);

        // Check duplicate phone but exclude current contact
        if (!empty($phone)) {
            $existing = $db->fetch("SELECT id, full_name FROM contacts WHERE phone = :phone AND id != :id", [':phone' => $phone, ':id' => $params['id']]);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => "این شماره قبلاً برای «{$existing->full_name}» ثبت شده است."]);
                exit;
            }
        }

        $newData = [
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
            'updated_by' => Auth::id(),
        ];
        $db->update('contacts', $newData, 'id = :id', [':id' => $params['id']]);
        AuditTrail::log('contact', $params['id'], 'update', $oldData, $newData);

        ActivityLog::log('update_contact', 'contact', $params['id'], "مخاطب {$fullName} ویرایش شد");
        Session::setFlash('success', 'مخاطب با موفقیت ویرایش شد.');
        View::redirect('/contacts/view/' . $params['id']);
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $contact = $db->fetch("SELECT * FROM contacts WHERE id = :id", [':id' => $params['id']]);
        if ($contact) {
            AuditTrail::log('contact', $params['id'], 'delete', (array)$contact, null);
            $db->delete('contacts', 'id = :id', [':id' => $params['id']]);
            ActivityLog::log('delete_contact', 'contact', $params['id'], "مخاطب {$contact->full_name} حذف شد");
            Session::setFlash('success', 'مخاطب با موفقیت حذف شد.');
        }
        View::redirect('/contacts');
    }
}