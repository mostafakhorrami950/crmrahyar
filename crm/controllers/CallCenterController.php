<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;

class CallCenterController
{
    /**
     * Dashboard: show current phone assignments and upcoming shifts
     */
    public function index(): void
    {
        $db = Database::getInstance();

        // Get all phone lines
        $phoneLines = $db->fetchAll("SELECT * FROM phone_lines WHERE is_active = 1 ORDER BY name");

        // Get current assignments (active right now)
        $now = date('Y-m-d H:i:s');
        $currentAssignments = $db->fetchAll(
            "SELECT pa.*, pl.name as line_name, pl.phone_number, u.full_name as user_name
             FROM phone_assignments pa
             JOIN phone_lines pl ON pa.phone_line_id = pl.id
             JOIN users u ON pa.user_id = u.id
             WHERE pa.status = 'active' AND pa.shift_start <= :now AND pa.shift_end >= :now
             ORDER BY pl.name",
            [':now' => $now]
        );

        // Get today's shifts
        $today = date('Y-m-d');
        $todayShifts = $db->fetchAll(
            "SELECT pa.*, pl.name as line_name, pl.phone_number, u.full_name as user_name
             FROM phone_assignments pa
             JOIN phone_lines pl ON pa.phone_line_id = pl.id
             JOIN users u ON pa.user_id = u.id
             WHERE DATE(pa.shift_start) = :today
             ORDER BY pa.shift_start",
            [':today' => $today]
        );

        // Get upcoming shifts (next 7 days)
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        $upcomingShifts = $db->fetchAll(
            "SELECT pa.*, pl.name as line_name, pl.phone_number, u.full_name as user_name
             FROM phone_assignments pa
             JOIN phone_lines pl ON pa.phone_line_id = pl.id
             JOIN users u ON pa.user_id = u.id
             WHERE pa.shift_start > :now AND DATE(pa.shift_start) <= :next
             ORDER BY pa.shift_start LIMIT 50",
            [':now' => $now, ':next' => $nextWeek]
        );

        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name");

        View::render('callcenter/index', [
            'title' => 'مدیریت کال سنتر',
            'phoneLines' => $phoneLines,
            'currentAssignments' => $currentAssignments,
            'todayShifts' => $todayShifts,
            'upcomingShifts' => $upcomingShifts,
            'users' => $users,
        ]);
    }

    /**
     * Manage phone lines (CRUD)
     */
    public function lines(): void
    {
        $db = Database::getInstance();
        $lines = $db->fetchAll("SELECT * FROM phone_lines ORDER BY name");
        View::render('callcenter/lines', [
            'title' => 'مدیریت خطوط تلفن',
            'lines' => $lines,
        ]);
    }

    /**
     * Add a new phone line
     */
    public function addLine(): void
    {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if (empty($name) || empty($phone)) {
            Session::setFlash('danger', 'نام و شماره تلفن الزامی است.');
            View::redirect('/callcenter/lines');
            return;
        }

        $db = Database::getInstance();
        $db->insert('phone_lines', [
            'name' => $name,
            'phone_number' => $phone,
            'description' => $desc,
        ]);

        Session::setFlash('success', 'خط تلفن جدید اضافه شد.');
        View::redirect('/callcenter/lines');
    }

    /**
     * Create a new shift assignment
     */
    public function assign(): void
    {
        $phoneLineId = (int)($_POST['phone_line_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);
        $shiftStart = $_POST['shift_start'] ?? '';
        $shiftEnd = $_POST['shift_end'] ?? '';
        $notes = trim($_POST['notes'] ?? '');

        if (!$phoneLineId || !$userId || !$shiftStart || !$shiftEnd) {
            Session::setFlash('danger', 'تمام فیلدها الزامی هستند.');
            View::redirect('/callcenter');
            return;
        }

        // Check for overlapping shifts
        $db = Database::getInstance();
        $overlap = $db->fetch(
            "SELECT pa.*, u.full_name as user_name, pl.name as line_name
             FROM phone_assignments pa
             JOIN users u ON pa.user_id = u.id
             JOIN phone_lines pl ON pa.phone_line_id = pl.id
             WHERE pa.phone_line_id = :lid AND pa.status = 'active'
             AND pa.shift_start < :end AND pa.shift_end > :start
             LIMIT 1",
            [':lid' => $phoneLineId, ':start' => $shiftStart, ':end' => $shiftEnd]
        );

        if ($overlap) {
            Session::setFlash('danger', "این خط تلفن در این بازه زمانی قبلاً به «{$overlap->user_name}» اختصاص داده شده.");
            View::redirect('/callcenter');
            return;
        }

        $db->insert('phone_assignments', [
            'phone_line_id' => $phoneLineId,
            'user_id' => $userId,
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);

        Session::setFlash('success', 'شیفت با موفقیت ثبت شد.');
        View::redirect('/callcenter');
    }

    /**
     * Cancel a shift assignment
     */
    public function cancel(array $params): void
    {
        $db = Database::getInstance();
        $db->update('phone_assignments', [
            'status' => 'cancelled',
        ], 'id = :id', [':id' => $params['id']]);

        Session::setFlash('success', 'شیفت لغو شد.');
        View::redirect('/callcenter');
    }

    /**
     * Delete a phone line
     */
    public function deleteLine(array $params): void
    {
        $db = Database::getInstance();
        $db->delete('phone_lines', 'id = :id', [':id' => $params['id']]);
        Session::setFlash('success', 'خط تلفن حذف شد.');
        View::redirect('/callcenter/lines');
    }
}