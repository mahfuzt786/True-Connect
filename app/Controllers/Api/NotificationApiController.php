<?php
namespace Api;

use Database;

class NotificationApiController extends ApiController {
    public function index(): void {
        $this->requireUser();
        $items = Database::fetchAll("SELECT * FROM notifications WHERE notifiable_type = 'User' AND notifiable_id = ? ORDER BY created_at DESC LIMIT 50", [$this->user['id']]);
        $unread = (int)(Database::fetch("SELECT COUNT(*) c FROM notifications WHERE notifiable_id = ? AND read_at IS NULL", [$this->user['id']])['c']);
        $this->ok(['items' => $items, 'unread' => $unread]);
    }
    public function read(string $id): void {
        $this->requireUser();
        Database::update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'id = ? AND notifiable_id = ?', [$id, $this->user['id']]);
        $this->ok(null, 'Marked as read');
    }
    public function readAll(): void {
        $this->requireUser();
        Database::query("UPDATE notifications SET read_at = NOW() WHERE notifiable_id = ? AND read_at IS NULL", [$this->user['id']]);
        $this->ok(null, 'All marked as read');
    }
}
