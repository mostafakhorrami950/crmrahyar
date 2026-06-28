<?php
namespace Controllers;

/**
 * PWA Controller
 * Handles Service Worker, Manifest, and Offline page serving
 * with proper headers for PWA functionality
 */
class PWAController
{
    /**
     * Serve Service Worker with correct headers
     */
    public function serviceWorker(): void
    {
        $swPath = __DIR__ . '/../public/sw.js';
        
        if (!file_exists($swPath)) {
            http_response_code(404);
            exit;
        }
        
        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($swPath);
        exit;
    }

    /**
     * Serve Manifest with correct headers
     */
    public function manifest(): void
    {
        $manifestPath = __DIR__ . '/../public/manifest.json';
        
        if (!file_exists($manifestPath)) {
            http_response_code(404);
            exit;
        }
        
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Access-Control-Allow-Origin: *');
        
        readfile($manifestPath);
        exit;
    }

    /**
     * Serve Offline page
     */
    public function offline(): void
    {
        $offlinePath = __DIR__ . '/../public/offline.html';
        
        if (!file_exists($offlinePath)) {
            http_response_code(404);
            echo '<h1>آفلاین</h1><p>شما به اینترنت متصل نیستید.</p>';
            exit;
        }
        
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache');
        
        readfile($offlinePath);
        exit;
    }

    /**
     * Serve PWA icons
     */
    public function icon(string $filename): void
    {
        $iconPath = __DIR__ . '/../public/assets/icons/' . basename($filename);
        
        if (!file_exists($iconPath)) {
            http_response_code(404);
            exit;
        }
        
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'ico' => 'image/x-icon'
        ];
        
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=604800');
        header('Access-Control-Allow-Origin: *');
        
        readfile($iconPath);
        exit;
    }

    /**
     * Push notification subscription endpoint
     * Stores push subscription for the authenticated user
     */
    public function subscribe(): void
    {
        header('Content-Type: application/json');
        
        if (!\Core\Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['endpoint'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid subscription data']);
            exit;
        }
        
        $db = \Core\Database::getInstance();
        $userId = \Core\Auth::id();
        
        // Check if subscription already exists
        $existing = $db->fetch(
            "SELECT id FROM push_subscriptions WHERE endpoint = ?",
            [$input['endpoint']]
        );
        
        if ($existing) {
            // Update existing subscription
            $db->query(
                "UPDATE push_subscriptions SET user_id = ?, p256dh = ?, auth = ?, updated_at = NOW() WHERE endpoint = ?",
                [$userId, $input['keys']['p256dh'] ?? '', $input['keys']['auth'] ?? '', $input['endpoint']]
            );
        } else {
            // Create push_subscriptions table if not exists
            $db->query("
                CREATE TABLE IF NOT EXISTS push_subscriptions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    endpoint TEXT NOT NULL,
                    p256dh VARCHAR(255) DEFAULT '',
                    auth VARCHAR(255) DEFAULT '',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_endpoint (endpoint(255))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $db->query(
                "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth) VALUES (?, ?, ?, ?)",
                [$userId, $input['endpoint'], $input['keys']['p256dh'] ?? '', $input['keys']['auth'] ?? '']
            );
        }
        
        echo json_encode(['success' => true, 'message' => 'اشتراک اعلان با موفقیت ثبت شد']);
        exit;
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(): void
    {
        header('Content-Type: application/json');
        
        if (!\Core\Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['endpoint'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }
        
        $db = \Core\Database::getInstance();
        $db->query("DELETE FROM push_subscriptions WHERE endpoint = ?", [$input['endpoint']]);
        
        echo json_encode(['success' => true]);
        exit;
    }
}