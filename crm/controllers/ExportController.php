<?php
namespace Controllers;

use Core\Auth;
use Core\Database;

class ExportController
{
    public function deals(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        
        $scope = Auth::scopeFilter('deals.view', ['d.assigned_to', 'd.created_by']);
        
        $deals = $db->fetchAll(
            "SELECT d.id, d.title, d.amount, d.source, d.is_won, d.is_lost, d.probability, d.created_at, d.closed_at,
                    s.name as stage, p.name as pipeline, c.full_name as contact, c.phone as contact_phone,
                    u.full_name as assigned_to
             FROM deals d 
             JOIN stages s ON d.stage_id=s.id JOIN pipelines p ON d.pipeline_id=p.id
             LEFT JOIN contacts c ON d.contact_id=c.id LEFT JOIN users u ON d.assigned_to=u.id
             WHERE {$scope['where']} ORDER BY d.id DESC",
            $scope['params']
        );
        
        $headers = ['شناسه','عنوان','مبلغ','منبع','وضعیت','احتمال','مرحله','پایپ‌لاین','مخاطب','تلفن','مسئول','تاریخ ایجاد','تاریخ بستن'];
        $rows = [];
        foreach ($deals as $d) {
            $status = $d->is_won ? 'موفق' : ($d->is_lost ? 'ناموفق' : 'در جریان');
            $rows[] = [$d->id, $d->title, $d->amount, $d->source, $status, $d->probability.'%', $d->stage, $d->pipeline, $d->contact, $d->contact_phone, $d->assigned_to, $d->created_at, $d->closed_at];
        }
        
        self::csv($headers, $rows, 'deals_' . date('Y-m-d'));
    }

    public function contacts(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY id DESC");
        
        $headers = ['شناسه','نام کامل','تلفن','ایمیل','کد ملی','پاسپورت','شرکت','آدرس','تاریخ ایجاد'];
        $rows = [];
        foreach ($contacts as $c) {
            $rows[] = [$c->id, $c->full_name, $c->phone, $c->email ?? '', $c->national_code ?? '', $c->passport_number ?? '', $c->company ?? '', $c->address ?? '', $c->created_at];
        }
        
        self::csv($headers, $rows, 'contacts_' . date('Y-m-d'));
    }

    public function payments(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $payments = $db->fetchAll(
            "SELECT p.*, d.title as deal_title, c.full_name as contact_name
             FROM payments p LEFT JOIN deals d ON p.deal_id=d.id LEFT JOIN contacts c ON d.contact_id=c.id
             ORDER BY p.id DESC"
        );
        
        $headers = ['شناسه','معامله','مخاطب','مبلغ','وضعیت','شناسه پیگیری','تاریخ'];
        $rows = [];
        foreach ($payments as $p) {
            $rows[] = [$p->id, $p->deal_title, $p->contact_name, $p->amount, $p->status, $p->track_id ?? '', $p->created_at];
        }
        
        self::csv($headers, $rows, 'payments_' . date('Y-m-d'));
    }

    public function users(): void
    {
        Auth::requireAuth();
        $db = Database::getInstance();
        $users = $db->fetchAll(
            "SELECT u.id, u.username, u.full_name, u.phone, u.email, r.name as role, u.is_active, u.created_at
             FROM users u LEFT JOIN roles r ON u.role_id=r.id ORDER BY u.id"
        );
        
        $headers = ['شناسه','نام کاربری','نام کامل','تلفن','ایمیل','نقش','فعال','تاریخ'];
        $rows = [];
        foreach ($users as $u) {
            $rows[] = [$u->id, $u->username, $u->full_name, $u->phone ?? '', $u->email ?? '', $u->role, $u->is_active ? 'بله' : 'خیر', $u->created_at];
        }
        
        self::csv($headers, $rows, 'users_' . date('Y-m-d'));
    }

    private static function csv(array $headers, array $rows, string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}