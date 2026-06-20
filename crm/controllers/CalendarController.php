<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\View;

class CalendarController
{
    public function index(): void
    {
        $month = (int)($_GET['month'] ?? date('n'));
        $year = (int)($_GET['year'] ?? date('Y'));
        
        if ($month < 1) { $month = 12; $year--; }
        if ($month > 12) { $month = 1; $year++; }
        
        $db = Database::getInstance();
        $userId = Auth::id();
        
        $scope = Auth::scopeFilter('deals.view', ['d.assigned_to', 'd.created_by']);
        $scopeWhere = $scope['where'] === '1=1' ? '' : " AND {$scope['where']}";
        
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $activities = $db->fetchAll(
            "SELECT da.*, d.title as deal_title, d.id as deal_id, u.full_name as user_name
             FROM deal_activities da 
             LEFT JOIN deals d ON da.deal_id = d.id
             LEFT JOIN users u ON da.user_id = u.id
             WHERE da.activity_date BETWEEN :start AND :end {$scopeWhere}
             ORDER BY da.activity_date ASC",
            array_merge([':start' => $startDate . ' 00:00:00', ':end' => $endDate . ' 23:59:59'], $scope['params'])
        );
        
        // Group by day
        $days = [];
        foreach ($activities as $act) {
            $day = (int)date('j', strtotime($act->activity_date));
            $days[$day][] = $act;
        }
        
        View::render('calendar/index', [
            'title' => 'تقویم فعالیت‌ها',
            'activities' => $activities,
            'days' => $days,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function events(): void
    {
        header('Content-Type: application/json');
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $db = Database::getInstance();
        
        $scope = Auth::scopeFilter('deals.view', ['d.assigned_to', 'd.created_by']);
        $scopeWhere = $scope['where'] === '1=1' ? '' : " AND {$scope['where']}";
        
        $activities = $db->fetchAll(
            "SELECT da.id, da.type, da.subject, da.activity_date, da.is_done, 
                    d.title as deal_title, d.id as deal_id
             FROM deal_activities da 
             LEFT JOIN deals d ON da.deal_id = d.id
             WHERE da.activity_date BETWEEN :start AND :end {$scopeWhere}
             ORDER BY da.activity_date ASC",
            array_merge([':start' => $start . ' 00:00:00', ':end' => $end . ' 23:59:59'], $scope['params'])
        );
        
        $events = [];
        foreach ($activities as $a) {
            $events[] = [
                'id' => $a->id,
                'title' => $a->subject . ' (' . $a->deal_title . ')',
                'start' => $a->activity_date,
                'color' => $a->is_done ? '#10B981' : '#3B82F6',
                'url' => '/deals/view/' . $a->deal_id,
                'type' => $a->type,
            ];
        }
        echo json_encode($events);
        exit;
    }
}