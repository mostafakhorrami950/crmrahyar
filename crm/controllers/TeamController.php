<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Session;
use Core\View;
use Core\ActivityLog;

class TeamController
{
    public function index(): void
    {
        $db = Database::getInstance();
        $teams = $db->fetchAll(
            "SELECT t.*, u.full_name as leader_name,
                    (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id) as member_count
             FROM teams t LEFT JOIN users u ON t.leader_id = u.id ORDER BY t.name"
        );
        View::render('teams/index', ['title' => 'مدیریت تیم‌ها', 'teams' => $teams]);
    }

    public function create(): void
    {
        $db = Database::getInstance();
        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name");
        View::render('teams/create', ['title' => 'ایجاد تیم جدید', 'users' => $users]);
    }

    public function store(): void
    {
        $db = Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $leaderId = (int)($_POST['leader_id'] ?? 0) ?: null;
        $members = $_POST['members'] ?? [];

        if (empty($name)) {
            Session::setFlash('danger', 'نام تیم الزامی است.');
            View::redirect('/teams/create');
        }

        $teamId = $db->insert('teams', ['name'=>$name, 'description'=>$desc, 'leader_id'=>$leaderId]);
        
        foreach ($members as $uid) {
            $db->insert('team_members', ['team_id'=>$teamId, 'user_id'=>(int)$uid]);
        }
        // Add leader as member too
        if ($leaderId && !in_array($leaderId, $members)) {
            $db->insert('team_members', ['team_id'=>$teamId, 'user_id'=>$leaderId]);
        }

        ActivityLog::log('create_team', 'team', $teamId, "تیم {$name} ایجاد شد");
        Session::setFlash('success', 'تیم با موفقیت ایجاد شد.');
        View::redirect('/teams');
    }

    public function edit(array $params): void
    {
        $db = Database::getInstance();
        $team = $db->fetch("SELECT * FROM teams WHERE id = :id", [':id'=>$params['id']]);
        if (!$team) { View::redirect('/teams'); return; }
        $users = $db->fetchAll("SELECT id, full_name FROM users WHERE is_active = 1 ORDER BY full_name");
        $memberIds = $db->fetchAll("SELECT user_id FROM team_members WHERE team_id = :tid", [':tid'=>$team->id]);
        $memberIds = array_map(fn($m) => $m->user_id, $memberIds);
        View::render('teams/edit', ['title'=>'ویرایش تیم', 'team'=>$team, 'users'=>$users, 'memberIds'=>$memberIds]);
    }

    public function update(array $params): void
    {
        $db = Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $leaderId = (int)($_POST['leader_id'] ?? 0) ?: null;
        $members = $_POST['members'] ?? [];

        $db->update('teams', ['name'=>$name, 'description'=>$desc, 'leader_id'=>$leaderId], 'id=:id', [':id'=>$params['id']]);
        $db->delete('team_members', 'team_id=:tid', [':tid'=>$params['id']]);
        foreach ($members as $uid) {
            $db->insert('team_members', ['team_id'=>$params['id'], 'user_id'=>(int)$uid]);
        }
        if ($leaderId && !in_array($leaderId, $members)) {
            $db->insert('team_members', ['team_id'=>$params['id'], 'user_id'=>$leaderId]);
        }

        Session::setFlash('success', 'تیم بروزرسانی شد.');
        View::redirect('/teams');
    }

    public function delete(array $params): void
    {
        $db = Database::getInstance();
        $db->delete('team_members', 'team_id=:tid', [':tid'=>$params['id']]);
        $db->delete('teams', 'id=:id', [':id'=>$params['id']]);
        Session::setFlash('success', 'تیم حذف شد.');
        View::redirect('/teams');
    }
}