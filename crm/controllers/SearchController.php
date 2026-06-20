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
            
            $results['contacts'] = $db->fetchAll(
                "SELECT id, full_name, phone, email, company FROM contacts 
                 WHERE full_name LIKE :q OR phone LIKE :q2 OR email LIKE :q3 OR national_code LIKE :q4 OR company LIKE :q5 LIMIT 20",
                [':q'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,':q5'=>$like]
            );
            
            $results['activities'] = $db->fetchAll(
                "SELECT da.id, da.type, da.subject, da.activity_date, d.title as deal_title, d.id as deal_id
                 FROM deal_activities da LEFT JOIN deals d ON da.deal_id=d.id
                 WHERE da.subject LIKE :q OR da.description LIKE :q2 LIMIT 15",
                [':q'=>$like,':q2'=>$like]
            );
            
            $results['payments'] = $db->fetchAll(
                "SELECT p.id, p.amount, p.status, p.created_at, d.title as deal_title, d.id as deal_id
                 FROM payments p LEFT JOIN deals d ON p.deal_id=d.id
                 WHERE d.title LIKE :q OR p.description LIKE :q2 LIMIT 15",
                [':q'=>$like,':q2'=>$like]
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
        
        $deals = $db->fetchAll("SELECT id, title FROM deals WHERE title LIKE :q LIMIT 5", [':q'=>$like]);
        $contacts = $db->fetchAll("SELECT id, full_name, phone FROM contacts WHERE full_name LIKE :q OR phone LIKE :q2 LIMIT 5", [':q'=>$like,':q2'=>$like]);
        
        echo json_encode(['deals'=>$deals, 'contacts'=>$contacts]);
        exit;
    }
}