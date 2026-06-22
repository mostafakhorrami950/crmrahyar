<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class DealController
{
    public function getData(array $params): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $deal = $db->fetch("SELECT * FROM deals WHERE id = :id", [':id' => $params['id']]);
        if (!$deal) {
            echo json_encode(['success' => false]);
            exit;
        }
        $stages = $db->fetchAll("SELECT s.* FROM stages s JOIN pipelines p ON s.pipeline_id = p.id WHERE p.id = :pid AND s.is_active = 1 ORDER BY s.order_index", [':pid' => $deal->pipeline_id]);
        
        $tags = [];
        if ($deal->description) {
            preg_match_all('/#([\x{600}-\x{6FF}\x{FB8A}\x{067E}\x{0686}\x{06AF}\x{0698}\w]+)/u', $deal->description, $matches);
            $tags = $matches[1] ?? [];
        }
        
        echo json_encode([
            'success' => true,
            'deal' => $deal,
            'stages' => $stages,
            'tags' => $tags
        ]);
        exit;
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $user = Auth::user();
        
        $search = $_GET['search'] ?? '';
        $stageId = $_GET['stage_id'] ?? '';
        $pipelineId = $_GET['pipeline_id'] ?? '';
        $assignedTo = $_GET['assigned_to'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        // Scope-based filtering using permission system
        $scope = Auth::scopeFilter('deals.view', ['d.assigned_to', 'd.created_by']);
        if ($scope['where'] !== '1=1') {
            $where .= " AND " . $scope['where'];
            $params = array_merge($params, $scope['params']);
        }
        
        if ($search) {
            $where .= " AND (d.title LIKE :search OR c.full_name LIKE :search2 OR c.phone LIKE :search3)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
        }
        if ($stageId) {
            $where .= " AND d.stage_id = :stage_id";
            $params[':stage_id'] = $stageId;
        }
        if ($pipelineId) {
            $where .= " AND d.pipeline_id = :pipeline_id";
            $params[':pipeline_id'] = $pipelineId;
        }
        if ($assignedTo) {
            $where .= " AND d.assigned_to = :assigned_to";
            $params[':assigned_to'] = $assignedTo;
        }
        if ($status === 'won') {
            $where .= " AND d.is_won = 1";
        } elseif ($status === 'lost') {
            $where .= " AND d.is_lost = 1";
        } elseif ($status === 'open') {
            $where .= " AND d.is_won = 0 AND d.is_lost = 0";
        }

        $deals = $db->fetchAll(
            "SELECT d.*, s.name as stage_name, s.color as stage_color, 
                    c.full_name as contact_name, c.phone as contact_phone,
                    p.name as pipeline_name, u.full_name as assigned_name
             FROM deals d 
             JOIN stages s ON d.stage_id = s.id 
             JOIN pipelines p ON d.pipeline_id = p.id 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             LEFT JOIN users u ON d.assigned_to = u.id 
             {$where}
             ORDER BY d.created_at DESC",
            $params
        );

        $pipelines = $db->fetchAll("SELECT id, name FROM pipelines WHERE is_active = 1");
        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1");
        $stages = $db->fetchAll("SELECT s.*, p.name as pipeline_name FROM stages s JOIN pipelines p ON s.pipeline_id = p.id WHERE s.is_active = 1 ORDER BY p.name, s.order_index");

        View::render('deals/index', [
            'title' => 'مدیریت معاملات',
            'deals' => $deals,
            'pipelines' => $pipelines,
            'users' => $users,
            'stages' => $stages,
            'search' => $search,
            'selectedStage' => $stageId,
            'selectedPipeline' => $pipelineId,
            'selectedAssigned' => $assignedTo,
            'selectedStatus' => $status,
        ]);
    }

    public function create(): void
    {
        $db = Database::getInstance();
        $pipelines = $db->fetchAll("SELECT * FROM pipelines WHERE is_active = 1");
        // Non-admin users see only their own contacts
        $cScope = Auth::scopeFilter('contacts.view', ['created_by']);
        $cScopeWhere = $cScope['where'] === '1=1' ? '' : "WHERE {$cScope['where']}";
        $contacts = $db->fetchAll("SELECT id, full_name, phone FROM contacts {$cScopeWhere} ORDER BY full_name", $cScope['params']);
        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1");
        $sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        
        $defaultPipeline = $db->fetch("SELECT id FROM pipelines WHERE is_default = 1");
        $stages = [];
        if ($defaultPipeline) {
            $stages = $db->fetchAll("SELECT * FROM stages WHERE pipeline_id = :id AND is_active = 1 ORDER BY order_index", [':id' => $defaultPipeline->id]);
        }

        View::render('deals/create', [
            'title' => 'ایجاد معامله جدید',
            'pipelines' => $pipelines,
            'contacts' => $contacts,
            'users' => $users,
            'stages' => $stages,
            'sources' => $sources,
        ]);
    }

    public function store(): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $amountRaw = str_replace(',', '', $_POST['amount'] ?? '');
        $amount = $amountRaw !== '' ? (int)$amountRaw : null;
        $pipelineId = (int)($_POST['pipeline_id'] ?? 0);
        $stageId = (int)($_POST['stage_id'] ?? 0);
        $contactId = (int)($_POST['contact_id'] ?? 0) ?: null;
        $assignedTo = (int)($_POST['assigned_to'] ?? 0) ?: null;
        $source = trim($_POST['source'] ?? '');
        $expectedCloseDate = $_POST['expected_close_date'] ?? null;

        // If user only has 'own' scope for deals.create, force assigned_to to themselves
        if (!Auth::canAccessAll('deals.create')) {
            $assignedTo = Auth::id();
        }

        if (empty($title) || empty($pipelineId) || empty($stageId)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'لطفا فیلدهای ضروری را پر کنید.']);
                exit;
            }
            Session::setFlash('danger', 'لطفا فیلدهای ضروری را پر کنید.');
            View::redirect('/deals/create');
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $dealId = $db->insert('deals', [
                'title' => $title,
                'description' => $description,
                'amount' => $amount,
                'pipeline_id' => $pipelineId,
                'stage_id' => $stageId,
                'contact_id' => $contactId,
                'assigned_to' => $assignedTo,
                'source' => $source,
                'expected_close_date' => $expectedCloseDate,
                'created_by' => Auth::id(),
            ]);

            $activityType = trim($_POST['activity_type'] ?? '');
            $activitySubject = trim($_POST['activity_subject'] ?? '');
            $activityDate = $_POST['activity_date'] ?? null;
            $activityDescription = trim($_POST['activity_description'] ?? '');
            $reminderAt = $_POST['reminder_at'] ?? null;

            if (!empty($activityType) && !empty($activityDate)) {
                $db->insert('deal_activities', [
                    'deal_id' => $dealId,
                    'user_id' => Auth::id(),
                    'type' => $activityType,
                    'subject' => $activitySubject ?: 'فعالیت برنامه‌ریزی شده',
                    'description' => $activityDescription,
                    'activity_date' => $activityDate,
                    'reminder_at' => $reminderAt ?: null,
                    'is_done' => 0,
                ]);
            }

            $db->commit();
            ActivityLog::log('create_deal', 'deal', $dealId, "معامله {$title} ایجاد شد");

            // Fire automation trigger: deal_created
            $contact = $contactId ? $db->fetch(
                "SELECT full_name as contact_name, phone as contact_phone, email as contact_email FROM contacts WHERE id = :id",
                [':id' => $contactId]
            ) : null;
            
            $stageInfo = $db->fetch("SELECT name as stage_name, pipeline_id FROM stages WHERE id = :id", [':id' => $stageId]);
            $pipelineInfo = $stageInfo ? $db->fetch("SELECT name as pipeline_name FROM pipelines WHERE id = :id", [':id' => $stageInfo->pipeline_id]) : null;
            
            $automationExtra = [
                'deal_id' => $dealId,
                'contact_id' => $contactId ?: 0,
                'contact_name' => $contact->contact_name ?? '',
                'contact_phone' => $contact->contact_phone ?? '',
                'contact_email' => $contact->contact_email ?? '',
                'title' => $title,
                'amount' => $amount ?? 0,
                'assigned_to' => $assignedTo ?: 0,
                'pipeline_id' => $pipelineId,
                'stage_id' => $stageId,
                'stage_name' => $stageInfo->stage_name ?? '',
                'pipeline_name' => $pipelineInfo->pipeline_name ?? '',
                'source' => $source,
            ];
            ob_start();
            \Controllers\AutomationController::execute('deal_created', 'deal', $dealId, $automationExtra);
            ob_end_clean();

            if ($isAjax) {
                echo json_encode([
                    'success' => true,
                    'message' => 'معامله با موفقیت ایجاد شد.',
                    'redirect' => '/deals/view/' . $dealId
                ]);
                exit;
            }
            Session::setFlash('success', 'معامله با موفقیت ایجاد شد.');
            View::redirect('/deals/view/' . $dealId);
        } catch (\Exception $e) {
            $db->rollback();
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]);
                exit;
            }
            Session::setFlash('danger', 'خطا: ' . $e->getMessage());
            View::redirect('/deals/create');
        }
    }

    public function view(array $params): void
    {
        $db = Database::getInstance();
        $deal = $db->fetch(
            "SELECT d.*, s.name as stage_name, s.color as stage_color, s.pipeline_id,
                    p.name as pipeline_name,
                    c.full_name as contact_name, c.phone as contact_phone, c.email as contact_email,
                    c.national_code, c.passport_number, c.address, c.company,
                    u.full_name as assigned_name, cr.full_name as creator_name
             FROM deals d 
             JOIN stages s ON d.stage_id = s.id 
             JOIN pipelines p ON d.pipeline_id = p.id 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             LEFT JOIN users u ON d.assigned_to = u.id 
             LEFT JOIN users cr ON d.created_by = cr.id 
             WHERE d.id = :id",
            [':id' => $params['id']]
        );

        if (!$deal) {
            Session::setFlash('danger', 'معامله مورد نظر یافت نشد.');
            View::redirect('/deals');
        }

        // Permission-based access check
        if (!Auth::canAccessAll('deals.view')) {
            $userId = Auth::id();
            if ($deal->assigned_to != $userId && $deal->created_by != $userId) {
                Session::setFlash('danger', 'شما فقط به معاملات خودتان دسترسی دارید.');
                View::redirect('/deals');
            }
        }

        $activities = $db->fetchAll(
            "SELECT da.*, u.full_name as user_name 
             FROM deal_activities da 
             LEFT JOIN users u ON da.user_id = u.id 
             WHERE da.deal_id = :id 
             ORDER BY da.activity_date DESC",
            [':id' => $params['id']]
        );

        $payments = $db->fetchAll(
            "SELECT * FROM payments WHERE deal_id = :id ORDER BY created_at DESC",
            [':id' => $params['id']]
        );

        $smsHistory = $db->fetchAll(
            "SELECT * FROM sms_history WHERE deal_id = :id ORDER BY created_at DESC",
            [':id' => $params['id']]
        );

        $logs = ActivityLog::getByEntity('deal', $params['id']);

        $allStages = $db->fetchAll(
            "SELECT * FROM stages WHERE pipeline_id = :pid AND is_active = 1 ORDER BY order_index",
            [':pid' => $deal->pipeline_id]
        );
        $currentOrder = 0;
        $totalStages = count($allStages);
        foreach ($allStages as $idx => $s) {
            if ($s->id == $deal->stage_id) { $currentOrder = $idx + 1; break; }
        }
        $progressPct = $totalStages > 0 ? round(($currentOrder / $totalStages) * 100) : 0;

        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1");
        $stages = $db->fetchAll("SELECT * FROM stages WHERE pipeline_id = :pid AND is_active = 1 ORDER BY order_index", [':pid' => $deal->pipeline_id]);

        View::render('deals/view', [
            'title' => "معامله: {$deal->title}",
            'deal' => $deal,
            'activities' => $activities,
            'payments' => $payments,
            'smsHistory' => $smsHistory,
            'logs' => $logs,
            'allStages' => $allStages,
            'currentOrder' => $currentOrder,
            'totalStages' => $totalStages,
            'progressPct' => $progressPct,
            'users' => $users,
            'stages' => $stages,
        ]);
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $deal = $db->fetch("SELECT * FROM deals WHERE id = :id", [':id' => $params['id']]);
        if (!$deal) {
            Session::setFlash('danger', 'معامله مورد نظر یافت نشد.');
            View::redirect('/deals');
        }

        // Permission-based access check for edit
        if (!Auth::canAccessAll('deals.edit')) {
            $userId = Auth::id();
            if ($deal->assigned_to != $userId && $deal->created_by != $userId) {
                Session::setFlash('danger', 'شما فقط می‌توانید معاملات خودتان را ویرایش کنید.');
                View::redirect('/deals');
            }
        }

        $pipelines = $db->fetchAll("SELECT * FROM pipelines WHERE is_active = 1");
        $stages = $db->fetchAll("SELECT * FROM stages WHERE pipeline_id = :id AND is_active = 1 ORDER BY order_index", [':id' => $deal->pipeline_id]);
        // Non-admin users see only their own contacts
        $cScope = Auth::scopeFilter('contacts.view', ['created_by']);
        $cScopeWhere = $cScope['where'] === '1=1' ? '' : "WHERE {$cScope['where']}";
        $contacts = $db->fetchAll("SELECT id, full_name, phone FROM contacts {$cScopeWhere} ORDER BY full_name", $cScope['params']);
        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1");
        $sources = $db->fetchAll("SELECT id, name, icon FROM deal_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");

        View::render('deals/edit', [
            'title' => 'ویرایش معامله',
            'deal' => $deal,
            'pipelines' => $pipelines,
            'stages' => $stages,
            'contacts' => $contacts,
            'users' => $users,
            'sources' => $sources,
        ]);
    }

    public function update(array $params): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $db = Database::getInstance();
        $existing = $db->fetch("SELECT * FROM deals WHERE id = :id", [':id' => $params['id']]);
        if (!$existing) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'معامله یافت نشد.']); exit; }
            Session::setFlash('danger', 'معامله یافت نشد.');
            View::redirect('/deals');
        }

        // Permission-based access check for update
        if (!Auth::canAccessAll('deals.edit')) {
            $userId = Auth::id();
            if ($existing->assigned_to != $userId && $existing->created_by != $userId) {
                if ($isAjax) { echo json_encode(['success' => false, 'message' => 'شما فقط می‌توانید معاملات خودتان را ویرایش کنید.']); exit; }
                Session::setFlash('danger', 'شما فقط می‌توانید معاملات خودتان را ویرایش کنید.');
                View::redirect('/deals');
            }
        }

        // For AJAX, keep existing values if not sent (partial update)
        $title = trim($_POST['title'] ?? $existing->title ?? '');
        $description = trim($_POST['description'] ?? $existing->description ?? '');
        $amountRaw = str_replace(',', '', $_POST['amount'] ?? '');
        $amount = $amountRaw !== '' ? (int)$amountRaw : ($existing->amount ?? null);
        $pipelineId = (int)($_POST['pipeline_id'] ?? $existing->pipeline_id ?? 0);
        $stageId = (int)($_POST['stage_id'] ?? $existing->stage_id ?? 0);
        $contactId = (int)($_POST['contact_id'] ?? 0) ?: $existing->contact_id ?: null;
        $assignedTo = (int)($_POST['assigned_to'] ?? 0) ?: $existing->assigned_to ?: null;
        
        // If user only has 'own' scope for deals.edit, prevent changing assigned_to
        if (!Auth::canAccessAll('deals.edit')) {
            $assignedTo = $existing->assigned_to; // Keep original
        }
        
        $source = trim($_POST['source'] ?? $existing->source ?? '');
        $expectedCloseDate = $_POST['expected_close_date'] ?? $existing->expected_close_date ?? null;
        $probability = (int)($_POST['probability'] ?? $existing->probability ?? 0);
        $lostReason = trim($_POST['lost_reason'] ?? $existing->lost_reason ?? '');
        $lossReasonId = (int)($_POST['loss_reason_id'] ?? $existing->loss_reason_id ?? 0) ?: null;
        $lossReasonNote = trim($_POST['loss_reason_note'] ?? $existing->loss_reason_note ?? '');
        $winReasonId = (int)($_POST['win_reason_id'] ?? $existing->win_reason_id ?? 0) ?: null;
        $winReasonNote = trim($_POST['win_reason_note'] ?? $existing->win_reason_note ?? '');
        $dealStatus = $_POST['deal_status'] ?? 'open';

        if (empty($title)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'عنوان معامله الزامی است.']); exit; }
            Session::setFlash('danger', 'عنوان معامله الزامی است.');
            View::redirect('/deals/edit/' . $params['id']);
        }

        $updateData = [
            'title' => $title,
            'description' => $description,
            'amount' => $amount,
            'pipeline_id' => $pipelineId,
            'stage_id' => $stageId,
            'contact_id' => $contactId,
            'assigned_to' => $assignedTo,
            'source' => $source,
            'expected_close_date' => $expectedCloseDate,
            'probability' => $probability,
            'lost_reason' => $lostReason,
            'loss_reason_id' => $lossReasonId,
            'loss_reason_note' => $lossReasonNote,
            'win_reason_id' => $winReasonId,
            'win_reason_note' => $winReasonNote,
        ];

        // Handle deal status - is_won, is_lost, or open
        if ($dealStatus === 'won') {
            $updateData['is_won'] = 1;
            $updateData['is_lost'] = 0;
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        } elseif ($dealStatus === 'lost') {
            $updateData['is_won'] = 0;
            $updateData['is_lost'] = 1;
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        } else {
            if (!$existing->is_won && !$existing->is_lost) {
                $updateData['is_won'] = 0;
                $updateData['is_lost'] = 0;
                $updateData['closed_at'] = null;
            }
        }

        $db->update('deals', $updateData, 'id = :id', [':id' => $params['id']]);

        ActivityLog::log('update_deal', 'deal', $params['id'], "معامله {$title} ویرایش شد");

        if ($isAjax) {
            echo json_encode(['success' => true, 'message' => 'معامله با موفقیت ویرایش شد.', 'redirect' => '/deals/view/' . $params['id']]);
            exit;
        }
        Session::setFlash('success', 'معامله با موفقیت ویرایش شد.');
        View::redirect('/deals/view/' . $params['id']);
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $deal = $db->fetch("SELECT * FROM deals WHERE id = :id", [':id' => $params['id']]);
        if (!$deal) {
            View::redirect('/deals');
        }

        // Permission-based access check for delete
        if (!Auth::canAccessAll('deals.delete')) {
            $userId = Auth::id();
            if ($deal->assigned_to != $userId && $deal->created_by != $userId) {
                Session::setFlash('danger', 'شما فقط می‌توانید معاملات خودتان را حذف کنید.');
                View::redirect('/deals');
            }
        }

        if ($deal) {
            $db->delete('deals', 'id = :id', [':id' => $params['id']]);
            ActivityLog::log('delete_deal', 'deal', $params['id'], "معامله {$deal->title} حذف شد");
            Session::setFlash('success', 'معامله با موفقیت حذف شد.');
        }
        View::redirect('/deals');
    }

    public function addActivity(array $params): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $type = $_POST['type'] ?? 'note';
        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $activityDate = $_POST['activity_date'] ?? date('Y-m-d H:i:s');
        $reminderAt = $_POST['reminder_at'] ?? null;

        $db = Database::getInstance();
        $db->insert('deal_activities', [
            'deal_id' => $params['id'],
            'user_id' => Auth::id(),
            'type' => $type,
            'subject' => $subject,
            'description' => $description,
            'activity_date' => $activityDate,
            'reminder_at' => $reminderAt ?: null,
        ]);

        ActivityLog::log('add_activity', 'deal', $params['id'], "فعالیت {$type} برای معامله ثبت شد");

        if ($isAjax) {
            echo json_encode(['success' => true, 'message' => 'فعالیت با موفقیت ثبت شد.', 'redirect' => '/deals/view/' . $params['id']]);
            exit;
        }
        Session::setFlash('success', 'فعالیت با موفقیت ثبت شد.');
        View::redirect('/deals/view/' . $params['id']);
    }

    public function allTags(): void
    {
        $db = Database::getInstance();
        $scope = Auth::scopeFilter('deals.view', ['assigned_to', 'created_by']);
        $scopeWhere = $scope['where'] === '1=1' ? '' : "AND {$scope['where']}";
        $deals = $db->fetchAll("SELECT id, description FROM deals WHERE description IS NOT NULL AND description != '' {$scopeWhere}", $scope['params']);
        
        $tags = [];
        foreach ($deals as $deal) {
            preg_match_all('/#([\x{600}-\x{6FF}\x{FB8A}\x{067E}\x{0686}\x{06AF}\x{0698}\w]+)/u', $deal->description, $matches);
            foreach ($matches[1] as $tag) {
                $tagLower = mb_strtolower($tag);
                if (!isset($tags[$tagLower])) {
                    $tags[$tagLower] = ['tag' => $tag, 'count' => 0, 'deal_ids' => []];
                }
                $tags[$tagLower]['count']++;
                $tags[$tagLower]['deal_ids'][] = $deal->id;
            }
        }
        ksort($tags);
        
        View::render('deals/tags', [
            'title' => 'همه هشتگ‌ها',
            'tags' => $tags,
        ]);
    }

    public function byTag(array $params): void
    {
        $tag = trim($params['tag'] ?? '');
        $db = Database::getInstance();
        
        // Also get other filter params from GET for search refinement
        $searchQuery = trim($_GET['search'] ?? '');
        $stageId = $_GET['stage_id'] ?? '';
        $pipelineId = $_GET['pipeline_id'] ?? '';
        $assignedTo = $_GET['assigned_to'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $scope = Auth::scopeFilter('deals.view', ['d.assigned_to', 'd.created_by']);
        $scopeWhere = $scope['where'] === '1=1' ? '' : "AND {$scope['where']}";
        $where = "WHERE d.description LIKE :tag1 {$scopeWhere}";
        $queryParams = array_merge([':tag1' => "%#{$tag}%"], $scope['params']);
        
        if ($searchQuery) {
            $where .= " AND (d.title LIKE :search OR c.full_name LIKE :search2)";
            $queryParams[':search'] = "%{$searchQuery}%";
            $queryParams[':search2'] = "%{$searchQuery}%";
        }
        if ($stageId) {
            $where .= " AND d.stage_id = :stage_id";
            $queryParams[':stage_id'] = $stageId;
        }
        if ($pipelineId) {
            $where .= " AND d.pipeline_id = :pipeline_id";
            $queryParams[':pipeline_id'] = $pipelineId;
        }
        if ($assignedTo) {
            $where .= " AND d.assigned_to = :assigned_to";
            $queryParams[':assigned_to'] = $assignedTo;
        }
        if ($status === 'won') {
            $where .= " AND d.is_won = 1";
        } elseif ($status === 'lost') {
            $where .= " AND d.is_lost = 1";
        } elseif ($status === 'open') {
            $where .= " AND d.is_won = 0 AND d.is_lost = 0";
        }
        
        $deals = $db->fetchAll(
            "SELECT d.*, s.name as stage_name, s.color as stage_color, 
                    c.full_name as contact_name, c.phone as contact_phone,
                    p.name as pipeline_name, u.full_name as assigned_name
             FROM deals d 
             JOIN stages s ON d.stage_id = s.id 
             JOIN pipelines p ON d.pipeline_id = p.id 
             LEFT JOIN contacts c ON d.contact_id = c.id 
             LEFT JOIN users u ON d.assigned_to = u.id 
             {$where}
             ORDER BY d.created_at DESC",
            $queryParams
        );

        $pipelines = $db->fetchAll("SELECT id, name FROM pipelines WHERE is_active = 1");
        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1");
        $stages = $db->fetchAll("SELECT s.*, p.name as pipeline_name FROM stages s JOIN pipelines p ON s.pipeline_id = p.id WHERE s.is_active = 1 ORDER BY p.name, s.order_index");

        View::render('deals/index', [
            'title' => "معاملات با تگ: #{$tag}",
            'deals' => $deals,
            'pipelines' => $pipelines,
            'users' => $users,
            'stages' => $stages,
            'search' => $searchQuery ?: "#{$tag}",
            'selectedStage' => $stageId,
            'selectedPipeline' => $pipelineId,
            'selectedAssigned' => $assignedTo,
            'selectedStatus' => $status,
        ]);
    }

    public function convertToDeal(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $title = trim($_POST['title'] ?? '');
        $pipelineId = (int)($_POST['pipeline_id'] ?? 0);
        $stageId = (int)($_POST['stage_id'] ?? 0);
        $contactName = trim($_POST['contact_name'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');

        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'عنوان معامله الزامی است']);
            exit;
        }

        $db->beginTransaction();
        try {
            $contactId = null;
            if ($contactPhone) {
                $contact = $db->fetch("SELECT id FROM contacts WHERE phone = :phone", [':phone' => $contactPhone]);
                if ($contact) {
                    $contactId = $contact->id;
                } elseif ($contactName) {
                    $contactId = $db->insert('contacts', [
                        'full_name' => $contactName,
                        'phone' => $contactPhone,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $dealId = $db->insert('deals', [
                'title' => $title,
                'pipeline_id' => $pipelineId,
                'stage_id' => $stageId,
                'contact_id' => $contactId,
                'created_by' => Auth::id(),
            ]);

            $db->commit();
            echo json_encode(['success' => true, 'deal_id' => $dealId]);
        } catch (\Exception $e) {
            $db->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}