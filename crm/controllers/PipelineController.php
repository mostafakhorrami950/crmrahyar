<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class PipelineController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $pipelines = $db->fetchAll(
            "SELECT p.*, (SELECT COUNT(*) FROM stages WHERE pipeline_id = p.id) as stages_count,
                    (SELECT COUNT(*) FROM deals WHERE pipeline_id = p.id) as deals_count
             FROM pipelines p ORDER BY p.created_at DESC"
        );
        View::render('pipelines/index', ['title' => 'مدیریت پایپ لاین‌ها', 'pipelines' => $pipelines]);
    }

    public function create(): void
    {
        View::render('pipelines/create', ['title' => 'ایجاد پایپ لاین جدید']);
    }

    public function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $stages = $_POST['stages'] ?? [];

        if (empty($name) || empty($stages)) {
            Session::setFlash('danger', 'لطفا نام و حداقل یک مرحله وارد کنید.');
            View::redirect('/pipelines/create');
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $pipelineId = $db->insert('pipelines', [
                'name' => $name,
                'description' => $description,
            ]);

            foreach ($stages as $index => $stage) {
                if (!empty(trim($stage['name']))) {
                    $db->insert('stages', [
                        'pipeline_id' => $pipelineId,
                        'name' => trim($stage['name']),
                        'description' => trim($stage['description'] ?? ''),
                        'color' => $stage['color'] ?? '#6B7280',
                        'order_index' => $index + 1,
                    ]);
                }
            }

            $db->commit();
            ActivityLog::log('create_pipeline', 'pipeline', $pipelineId, "پایپ لاین {$name} ایجاد شد");
            Session::setFlash('success', 'پایپ لاین با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            $db->rollback();
            Session::setFlash('danger', 'خطا در ایجاد پایپ لاین: ' . $e->getMessage());
        }

        View::redirect('/pipelines');
    }

    public function stages(array $params): void
    {
        $db = Database::getInstance();
        $pipeline = $db->fetch("SELECT * FROM pipelines WHERE id = :id", [':id' => $params['id']]);
        if (!$pipeline) {
            echo json_encode(['success' => false, 'message' => 'پایپ لاین یافت نشد']);
            exit;
        }
        $stages = $db->fetchAll("SELECT * FROM stages WHERE pipeline_id = :id ORDER BY order_index", [':id' => $params['id']]);
        echo json_encode(['success' => true, 'stages' => $stages]);
        exit;
    }

    public function storeStage(array $params): void
    {
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#6B7280');
        $pipelineId = $params['id'] ?? 0;
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'نام مرحله الزامی است']);
            exit;
        }
        
        $db = Database::getInstance();
        $maxOrder = $db->fetch("SELECT COALESCE(MAX(order_index), 0) as max_order FROM stages WHERE pipeline_id = :id", [':id' => $pipelineId]);
        $stageId = $db->insert('stages', [
            'pipeline_id' => $pipelineId,
            'name' => $name,
            'color' => $color,
            'order_index' => ($maxOrder->max_order ?? 0) + 1,
        ]);
        
        ActivityLog::log('create_stage', 'stage', $stageId, "مرحله {$name} ایجاد شد");
        echo json_encode(['success' => true, 'message' => 'مرحله با موفقیت ایجاد شد', 'stage_id' => $stageId]);
        exit;
    }

    public function updateStageName(array $params): void
    {
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#6B7280');
        $stageId = $params['id'] ?? 0;
        
        if (empty($name) || !$stageId) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر']);
            exit;
        }
        
        $db = Database::getInstance();
        $db->update('stages', ['name' => $name, 'color' => $color], 'id = :id', [':id' => $stageId]);
        echo json_encode(['success' => true, 'message' => 'مرحله ویرایش شد']);
        exit;
    }

    public function deleteStage(array $params): void
    {
        $stageId = $params['id'] ?? 0;
        if (!$stageId) {
            echo json_encode(['success' => false, 'message' => 'مرحله نامعتبر']);
            exit;
        }
        
        $db = Database::getInstance();
        // Check if any deals exist in this stage
        $dealCount = $db->fetch("SELECT COUNT(*) as count FROM deals WHERE stage_id = :id", [':id' => $stageId]);
        if ($dealCount->count > 0) {
            echo json_encode(['success' => false, 'message' => 'این مرحله دارای معامله است و قابل حذف نیست']);
            exit;
        }
        $db->delete('stages', 'id = :id', [':id' => $stageId]);
        echo json_encode(['success' => true, 'message' => 'مرحله حذف شد']);
        exit;
    }

    public function reorderStages(array $params): void
    {
        $order = $_POST['order'] ?? []; // [stage_id => order_index]
        if (empty($order)) {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر']);
            exit;
        }
        $db = Database::getInstance();
        foreach ($order as $stageId => $index) {
            $db->update('stages', ['order_index' => (int)$index + 1], 'id = :id', [':id' => (int)$stageId]);
        }
        echo json_encode(['success' => true, 'message' => 'ترتیب مراحل بروزرسانی شد']);
        exit;
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $pipeline = $db->fetch("SELECT * FROM pipelines WHERE id = :id", [':id' => $params['id']]);
        if (!$pipeline) {
            Session::setFlash('danger', 'پایپ لاین مورد نظر یافت نشد.');
            View::redirect('/pipelines');
        }
        $stages = $db->fetchAll("SELECT * FROM stages WHERE pipeline_id = :id ORDER BY order_index", [':id' => $params['id']]);
        View::render('pipelines/edit', ['title' => 'ویرایش پایپ لاین', 'pipeline' => $pipeline, 'stages' => $stages]);
    }

    public function update(array $params): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $stages = $_POST['stages'] ?? [];

        if (empty($name)) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'نام پایپ لاین الزامی است']); exit; }
            Session::setFlash('danger', 'نام پایپ لاین الزامی است.');
            View::redirect('/pipelines/edit/' . $params['id']);
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $db->update('pipelines', ['name' => $name, 'description' => $description], 'id = :id', [':id' => $params['id']]);

            // Get existing stage IDs
            $existingStages = $db->fetchAll("SELECT id FROM stages WHERE pipeline_id = :id", [':id' => $params['id']]);
            $existingIds = array_column($existingStages, 'id');
            $usedIds = [];

            foreach ($stages as $index => $stage) {
                if (empty(trim($stage['name'] ?? ''))) continue;
                
                $stageData = [
                    'pipeline_id' => $params['id'],
                    'name' => trim($stage['name']),
                    'description' => trim($stage['description'] ?? ''),
                    'color' => $stage['color'] ?? '#6B7280',
                    'order_index' => $index + 1,
                ];

                if (!empty($stage['id']) && in_array((int)$stage['id'], $existingIds)) {
                    // Update existing stage
                    $db->update('stages', $stageData, 'id = :id', [':id' => (int)$stage['id']]);
                    $usedIds[] = (int)$stage['id'];
                } else {
                    // Insert new stage
                    $newId = $db->insert('stages', $stageData);
                    $usedIds[] = $newId;
                }
            }

            $db->commit();
            ActivityLog::log('update_pipeline', 'pipeline', $params['id'], "پایپ لاین {$name} ویرایش شد");

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'پایپ لاین با موفقیت ویرایش شد.', 'redirect' => '/pipelines']);
                exit;
            }
            Session::setFlash('success', 'پایپ لاین با موفقیت ویرایش شد.');
        } catch (\Exception $e) {
            $db->rollback();
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'خطا: ' . $e->getMessage()]); exit; }
            Session::setFlash('danger', 'خطا در ویرایش: ' . $e->getMessage());
        }

        View::redirect('/pipelines');
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $pipeline = $db->fetch("SELECT name FROM pipelines WHERE id = :id", [':id' => $params['id']]);
        
        if ($pipeline) {
            $db->delete('pipelines', 'id = :id', [':id' => $params['id']]);
            ActivityLog::log('delete_pipeline', 'pipeline', $params['id'], "پایپ لاین {$pipeline->name} حذف شد");
            Session::setFlash('success', 'پایپ لاین با موفقیت حذف شد.');
        }
        View::redirect('/pipelines');
    }

    public function kanban(array $params): void
    {
        $db = Database::getInstance();
        $pipeline = $db->fetch("SELECT * FROM pipelines WHERE id = :id AND is_active = 1", [':id' => $params['id']]);
        if (!$pipeline) {
            Session::setFlash('danger', 'پایپ لاین مورد نظر یافت نشد.');
            View::redirect('/pipelines');
        }

        $stages = $db->fetchAll("SELECT * FROM stages WHERE pipeline_id = :id AND is_active = 1 ORDER BY order_index", [':id' => $params['id']]);
        
        $user = Auth::user();
        $deals = [];
        foreach ($stages as $stage) {
            if ($user->role_slug === 'operator') {
                $deals[$stage->id] = $db->fetchAll(
                    "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone, 
                            u.full_name as assigned_name
                     FROM deals d 
                     LEFT JOIN contacts c ON d.contact_id = c.id 
                     LEFT JOIN users u ON d.assigned_to = u.id 
                     WHERE d.stage_id = :stage_id AND d.is_lost = 0 AND d.is_won = 0
                     AND (d.assigned_to = :user_id OR d.created_by = :user_id2)
                     ORDER BY d.updated_at DESC",
                    [':stage_id' => $stage->id, ':user_id' => $user->id, ':user_id2' => $user->id]
                );
            } else {
                $deals[$stage->id] = $db->fetchAll(
                    "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone,
                            u.full_name as assigned_name
                     FROM deals d 
                     LEFT JOIN contacts c ON d.contact_id = c.id 
                     LEFT JOIN users u ON d.assigned_to = u.id 
                     WHERE d.stage_id = :stage_id AND d.is_lost = 0 AND d.is_won = 0
                     ORDER BY d.updated_at DESC",
                    [':stage_id' => $stage->id]
                );
            }
        }

        $pipelines = $db->fetchAll("SELECT id, name FROM pipelines WHERE is_active = 1");

        View::render('pipelines/kanban', [
            'title' => "کانبان - {$pipeline->name}",
            'pipeline' => $pipeline,
            'stages' => $stages,
            'deals' => $deals,
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * API: Return all pipelines with their stages as JSON
     */
    public function apiAll(): void
    {
        header('Content-Type: application/json');
        $db = Database::getInstance();
        $pipelines = $db->fetchAll(
            "SELECT p.id, p.name, p.description, p.is_active
             FROM pipelines p ORDER BY p.is_active DESC, p.name ASC"
        );
        foreach ($pipelines as &$p) {
            $p->stages = $db->fetchAll(
                "SELECT id, name, color, order_index FROM stages WHERE pipeline_id = :pid ORDER BY order_index ASC",
                [':pid' => $p->id]
            );
        }
        echo json_encode(['success' => true, 'pipelines' => $pipelines]);
        exit;
    }

    public function updateStage(): void
    {
        $dealId = (int)($_POST['deal_id'] ?? 0);
        $stageId = (int)($_POST['stage_id'] ?? 0);

        if ($dealId && $stageId) {
            // Check ownership for non-admin users
            if (!Auth::ownsDeal($dealId)) {
                echo json_encode(['success' => false, 'message' => 'شما فقط به معاملات خودتان دسترسی دارید.']);
                exit;
            }
            
            $db = Database::getInstance();
            $db->update('deals', ['stage_id' => $stageId], 'id = :id', [':id' => $dealId]);
            
            // Check if this stage is the "won" or "lost" stage for auto-update
            $stage = $db->fetch("SELECT s.name, p.name as pipeline_name FROM stages s JOIN pipelines p ON s.pipeline_id = p.id WHERE s.id = :id", [':id' => $stageId]);
            
            $isWon = stripos($stage->name, 'پرداخت شده') !== false || stripos($stage->name, 'موفق') !== false;
            $isLost = stripos($stage->name, 'لغو') !== false;
            
            if ($isWon) {
                $db->update('deals', ['is_won' => 1, 'closed_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $dealId]);
            } elseif ($isLost) {
                $db->update('deals', ['is_lost' => 1, 'closed_at' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $dealId]);
            }

            ActivityLog::log('move_deal', 'deal', $dealId, "معامله به مرحله {$stage->name} منتقل شد");
            
            // Trigger automation engine (suppress warnings to protect JSON response)
            $deal = $db->fetch(
                "SELECT d.*, c.full_name as contact_name, c.phone as contact_phone,
                        p.name as pipeline_name
                 FROM deals d 
                 LEFT JOIN contacts c ON d.contact_id = c.id
                 LEFT JOIN pipelines p ON d.pipeline_id = p.id
                 WHERE d.id = :id",
                [':id' => $dealId]
            );
            
            $extra = [
                'stage_id' => $stageId,
                'stage_name' => $stage->name,
                'pipeline_id' => $deal->pipeline_id ?? 0,
                'pipeline_name' => $stage->pipeline_name ?? '',
                'contact_name' => $deal->contact_name ?? '',
                'contact_phone' => $deal->contact_phone ?? '',
                'contact_id' => $deal->contact_id ?? 0,
                'title' => $deal->title ?? '',
                'amount' => $deal->amount ?? 0,
                'assigned_to' => $deal->assigned_to ?? 0,
                'deal_id' => $dealId,
            ];
            
            // Use output buffering to capture any warnings/errors from automation
            ob_start();
            \Controllers\AutomationController::execute('stage_change', 'deal', $dealId, $extra);
            if ($isWon) {
                \Controllers\AutomationController::execute('deal_won', 'deal', $dealId, $extra);
            } elseif ($isLost) {
                \Controllers\AutomationController::execute('deal_lost', 'deal', $dealId, $extra);
            }
            $automationOutput = ob_get_clean();
            
            // Log any captured warnings/errors
            if (!empty(trim($automationOutput))) {
                \Core\Logger::error("Automation warnings for deal {$dealId}: " . $automationOutput);
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'اطلاعات نامعتبر']);
        }
        exit;
    }
}