<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\View;

class SearchController
{
    public function index(): void
    {
        Auth::requireAuth();
        $q = trim($_GET['q'] ?? '');
        $results = ['deals' => [], 'contacts' => [], 'activities' => [], 'payments' => []];
        
        if (strlen($q) >= 2) {
            $db = Database::getInstance();
            $like = "%{$q}%";
            
            $scope = Auth::scopeFilter('deals.view', ['d.assigned_to', 'd.created_by']);
            $scopeWhere = $scope['where'] === '1=1' ? '' : " AND {$scope['where']}";
            
            $results['deals'] = $db->fetchAll(
                "SELECT d.id, d.title, d.amount, d.is_won, d.is_lost, s.name as stage_name, c.full_name as contact_name 
                 FROM deals d JOIN stages s ON d.stage_id=s.id LEFT JOIN contacts c ON d.contact_id=c.id 
                 WHERE (d.title LIKE :q OR d.description LIKE :q2 OR c.full_name LIKE :q3) {$scopeWhere} LIMIT 20",
                array_merge([':q'=>$like,':q2'=>$like,':q3'=>$like], $scope['params'])
            );
            
            // Contacts: scope filter
            $cScope = Auth::scopeFilter('contacts.view', ['created_by']);
            $cScopeWhere = $cScope['where'] === '1=1' ? '' : " AND {$cScope['where']}";
            $results['contacts'] = $db->fetchAll(
                "SELECT id, full_name, phone, email, company FROM contacts 
                 WHERE (full_name LIKE :q OR phone LIKE :q2 OR email LIKE :q3 OR national_code LIKE :q4 OR company LIKE :q5) {$cScopeWhere} LIMIT 20",
                array_merge([':q'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,':q5'=>$like], $cScope['params'])
            );
            
            // Activities: filter by current user for non-admin
            $actUserFilter = '';
            $actParams = [':q'=>$like,':q2'=>$like];
            if (!Auth::hasPermission('settings.manage')) {
                $actUserFilter = " AND da.user_id = :act_uid";
                $actParams[':act_uid'] = Auth::id();
            }
            $results['activities'] = $db->fetchAll(
                "SELECT da.id, da.type, da.subject, da.activity_date, d.title as deal_title, d.id as deal_id
                 FROM deal_activities da LEFT JOIN deals d ON da.deal_id=d.id
                 WHERE (da.subject LIKE :q OR da.description LIKE :q2) {$actUserFilter} LIMIT 15",
                $actParams
            );
            
            // Payments: scope filter via deal
            $pScope = Auth::scopeFilter('payments.view', ['d.assigned_to', 'd.created_by']);
            $pScopeWhere = $pScope['where'] === '1=1' ? '' : " AND {$pScope['where']}";
            $results['payments'] = $db->fetchAll(
                "SELECT p.id, p.amount, p.status, p.created_at, d.title as deal_title, d.id as deal_id
                 FROM payments p LEFT JOIN deals d ON p.deal_id=d.id
                 WHERE (d.title LIKE :q OR p.description LIKE :q2) {$pScopeWhere} LIMIT 15",
                array_merge([':q'=>$like,':q2'=>$like], $pScope['params'])
            );
        }
        
        View::render('search/index', [
            'title' => 'جستجو: ' . htmlspecialchars($q),
            'query' => $q,
            'results' => $results,
        ]);
    }

    public function api(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode(['deals'=>[], 'contacts'=>[]]);
            exit;
        }
        $db = Database::getInstance();
        $like = "%{$q}%";
        
        $scope = Auth::scopeFilter('deals.view', ['assigned_to', 'created_by']);
        $scopeWhere = $scope['where'] === '1=1' ? '' : " AND {$scope['where']}";
        $deals = $db->fetchAll("SELECT id, title FROM deals WHERE title LIKE :q {$scopeWhere} LIMIT 5", array_merge([':q'=>$like], $scope['params']));
        
        $cScope = Auth::scopeFilter('contacts.view', ['created_by']);
        $cScopeWhere = $cScope['where'] === '1=1' ? '' : " AND {$cScope['where']}";
        $contacts = $db->fetchAll("SELECT id, full_name, phone FROM contacts WHERE (full_name LIKE :q OR phone LIKE :q2) {$cScopeWhere} LIMIT 5", array_merge([':q'=>$like,':q2'=>$like], $cScope['params']));
        
        echo json_encode(['deals'=>$deals, 'contacts'=>$contacts]);
        exit;
    }
}