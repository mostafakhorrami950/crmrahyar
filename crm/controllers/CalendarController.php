<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\JDate;

class CalendarController
{
    public function index(): void
    {
        // Current Jalali date as default
        list($currentJYear, $currentJMonth, $currentJDay) = JDate::now();
        
        $month = (int)($_GET['month'] ?? $currentJMonth);
        $year = (int)($_GET['year'] ?? $currentJYear);
        
        if ($month < 1) { $month = 12; $year--; }
        if ($month > 12) { $month = 1; $year++; }
        
        $db = Database::getInstance();
        $userId = Auth::id();
        
        $scope = Auth::scopeFilter('deals.view', ['d.assigned_to', 'd.created_by']);
        $scopeWhere = $scope['where'] === '1=1' ? '' : " AND {$scope['where']}";
        
        // Convert Jalali year/month to Gregorian date range for DB query
        list($gy1, $gm1, $gd1) = JDate::toGregorian($year, $month, 1);
        $daysInMonth = JDate::daysInMonth($year, $month);
        list($gy2, $gm2, $gd2) = JDate::toGregorian($year, $month, $daysInMonth);
        
        $startDate = sprintf('%04d-%02d-%02d', $gy1, $gm1, $gd1);
        $endDate = sprintf('%04d-%02d-%02d', $gy2, $gm2, $gd2);
        
        $activities = $db->fetchAll(
            "SELECT da.*, d.title as deal_title, d.id as deal_id, u.full_name as user_name
             FROM deal_activities da 
             LEFT JOIN deals d ON da.deal_id = d.id
             LEFT JOIN users u ON da.user_id = u.id
             WHERE da.activity_date BETWEEN :start AND :end {$scopeWhere}
             ORDER BY da.activity_date ASC",
            array_merge([':start' => $startDate . ' 00:00:00', ':end' => $endDate . ' 23:59:59'], $scope['params'])
        );
        
        // Group by Jalali day
        $days = [];
        foreach ($activities as $act) {
            $actDate = explode(' ', $act->activity_date)[0] ?? '';
            $actDateParts = explode('-', $actDate);
            if (count($actDateParts) === 3) {
                list(, , $jDay) = JDate::toJalali((int)$actDateParts[0], (int)$actDateParts[1], (int)$actDateParts[2]);
                $days[$jDay][] = $act;
            }
        }
        
        $monthName = JDate::monthName($month);
        
        View::render('calendar/index', [
            'title' => "تقویم {$monthName} {$year}",
            'activities' => $activities,
            'days' => $days,
            'month' => $month,
            'year' => $year,
            'monthName' => $monthName,
            'daysInMonth' => $daysInMonth,
        ]);
    }

    public function events(): void
    {
        header('Content-Type: application/json');
        
        // Accept Jalali year/month and convert to Gregorian
        $jYear = (int)($_GET['jyear'] ?? 0);
        $jMonth = (int)($_GET['jmonth'] ?? 0);
        
        if ($jYear > 0 && $jMonth > 0) {
            list($gy1, $gm1, $gd1) = JDate::toGregorian($jYear, $jMonth, 1);
            $daysInMonth = JDate::daysInMonth($jYear, $jMonth);
            list($gy2, $gm2, $gd2) = JDate::toGregorian($jYear, $jMonth, $daysInMonth);
            $start = sprintf('%04d-%02d-%02d', $gy1, $gm1, $gd1);
            $end = sprintf('%04d-%02d-%02d', $gy2, $gm2, $gd2);
        } else {
            // Fallback: Gregorian dates directly
            $start = $_GET['start'] ?? date('Y-m-01');
            $end = $_GET['end'] ?? date('Y-m-t');
        }
        
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
            // Convert activity date to Jalali for display
            $actDate = explode(' ', $a->activity_date)[0] ?? '';
            $actDateParts = explode('-', $actDate);
            $jalaliDate = '';
            if (count($actDateParts) === 3) {
                $jalaliDate = JDate::displayDate($a->activity_date);
            }
            
            $events[] = [
                'id' => $a->id,
                'title' => $a->subject . ' (' . $a->deal_title . ')',
                'start' => $a->activity_date,
                'jalali_date' => $jalaliDate,
                'color' => $a->is_done ? '#10B981' : '#3B82F6',
                'url' => '/deals/view/' . $a->deal_id,
                'type' => $a->type,
            ];
        }
        echo json_encode($events);
        exit;
    }
}
