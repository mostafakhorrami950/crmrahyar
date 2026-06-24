<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;

class TargetController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('n'));
        
        // Update achieved amounts from actual data
        $this->syncAchievements($db, $year, $month);
        
        // Non-admin users can only see their own targets
        $isAdmin = Auth::hasPermission('settings.manage');
        if ($isAdmin) {
            $targets = $db->fetchAll(
                "SELECT st.*, 
                        CASE WHEN st.target_type = 'user' THEN u.full_name ELSE t.name END as target_name
                 FROM sales_targets st
                 LEFT JOIN users u ON st.target_type = 'user' AND st.target_id = u.id
                 LEFT JOIN teams t ON st.target_type = 'team' AND st.target_id = t.id
                 WHERE st.year = :year AND st.month = :month
                 ORDER BY st.target_type, target_name",
                [':year' => $year, ':month' => $month]
            );
        } else {
            $userId = Auth::id();
            $targets = $db->fetchAll(
                "SELECT st.*, 
                        CASE WHEN st.target_type = 'user' THEN u.full_name ELSE t.name END as target_name
                 FROM sales_targets st
                 LEFT JOIN users u ON st.target_type = 'user' AND st.target_id = u.id
                 LEFT JOIN teams t ON st.target_type = 'team' AND st.target_id = t.id
                 WHERE st.year = :year AND st.month = :month
                   AND ((st.target_type = 'user' AND st.target_id = :user_id)
                     OR (st.target_type = 'team' AND st.target_id IN (SELECT team_id FROM team_members WHERE user_id = :user_id2)))
                 ORDER BY st.target_type, target_name",
                [':year' => $year, ':month' => $month, ':user_id' => $userId, ':user_id2' => $userId]
            );
        }
        
        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name");
        $teams = $db->fetchAll("SELECT id, name FROM teams WHERE is_active = 1 ORDER BY name");
        
        View::render('targets/index', [
            'title' => 'هدف‌گذاری فروش',
            'targets' => $targets,
            'users' => $users,
            'teams' => $teams,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function store(): void
    {
        $db = Database::getInstance();
        $type = $_POST['target_type'] ?? 'user';
        $targetId = (int)($_POST['target_id'] ?? 0);
        $year = (int)($_POST['year'] ?? date('Y'));
        $month = (int)($_POST['month'] ?? date('n'));
        $amount = (int)($_POST['target_amount'] ?? 0);
        $deals = (int)($_POST['target_deals'] ?? 0);
        $dateFrom = !empty($_POST['date_from']) ? $_POST['date_from'] : null;
        $dateTo = !empty($_POST['date_to']) ? $_POST['date_to'] : null;
        
        // Check if exists
        $existing = $db->fetch(
            "SELECT id FROM sales_targets WHERE target_type=:t AND target_id=:tid AND year=:y AND month=:m",
            [':t'=>$type, ':tid'=>$targetId, ':y'=>$year, ':m'=>$month]
        );
        
        $data = [
            'target_amount'=>$amount, 
            'target_deals'=>$deals,
            'date_from'=>$dateFrom,
            'date_to'=>$dateTo,
        ];
        
        if ($existing) {
            $db->update('sales_targets', $data, 'id=:id', [':id'=>$existing->id]);
        } else {
            $data['target_type'] = $type;
            $data['target_id'] = $targetId;
            $data['year'] = $year;
            $data['month'] = $month;
            $db->insert('sales_targets', $data);
        }
        
        Session::setFlash('success', 'هدف فروش ذخیره شد.');
        View::redirect("/targets?year={$year}&month={$month}");
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $db->delete('sales_targets', 'id=:id', [':id'=>$params['id']]);
        Session::setFlash('success', 'هدف حذف شد.');
        View::redirect('/targets');
    }

    private function syncAchievements(Database $db, int $year, int $month): void
    {
        $targets = $db->fetchAll("SELECT * FROM sales_targets WHERE year=:y AND month=:m", [':y'=>$year, ':m'=>$month]);
        foreach ($targets as $t) {
            // Use custom date range if set, otherwise use month defaults
            if (!empty($t->date_from) && !empty($t->date_to)) {
                $startDatetime = $t->date_from . ' 00:00:00';
                $endDatetime = $t->date_to . ' 23:59:59';
            } else {
                $startDate = sprintf('%04d-%02d-01', $year, $month);
                $endDate = date('Y-m-t', strtotime($startDate));
                $startDatetime = $startDate . ' 00:00:00';
                $endDatetime = $endDate . ' 23:59:59';
            }
            
            if ($t->target_type === 'user') {
                $result = $db->fetch(
                    "SELECT COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM deals 
                     WHERE assigned_to=:uid AND is_won=1
                     AND COALESCE(closed_at, updated_at, created_at) BETWEEN :s AND :e",
                    [':uid'=>$t->target_id, ':s'=>$startDatetime, ':e'=>$endDatetime]
                );
            } else {
                $result = $db->fetch(
                    "SELECT COALESCE(SUM(d.amount),0) as total, COUNT(*) as cnt FROM deals d
                     JOIN team_members tm ON d.assigned_to = tm.user_id
                     WHERE tm.team_id=:tid AND d.is_won=1
                     AND COALESCE(d.closed_at, d.updated_at, d.created_at) BETWEEN :s AND :e",
                    [':tid'=>$t->target_id, ':s'=>$startDatetime, ':e'=>$endDatetime]
                );
            }
            $db->update('sales_targets', [
                'achieved_amount' => (int)($result->total ?? 0),
                'achieved_deals' => (int)($result->cnt ?? 0),
            ], 'id=:id', [':id'=>$t->id]);
        }
    }
}