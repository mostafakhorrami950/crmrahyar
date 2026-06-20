<?php
namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Notification;
use Core\View;

class NotificationController
{
    public function index(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $data = Notification::getAll(Auth::id(), $page, 20);
        View::render('notifications/index', [
            'title' => 'اعلان‌ها',
            'notifications' => $data['items'],
            'total' => $data['total'],
            'page' => $data['page'],
            'perPage' => $data['perPage'],
        ]);
    }

    public function unread(): void
    {
        $items = Notification::getUnread(Auth::id(), 50);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notifications' => $items, 'count' => count($items)]);
        exit;
    }

    public function markRead(array $params): void
    {
        Notification::markRead((int)$params['id'], Auth::id());
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function markAllRead(): void
    {
        Notification::markAllRead(Auth::id());
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        \Core\Session::setFlash('success', 'همه اعلان‌ها خوانده شدند.');
        View::redirect('/notifications');
    }
}