<?php

class NotificationController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void {
        $notifications = Database::fetchAll(
            "SELECT * FROM notifications WHERE notifiable_type = 'User' AND notifiable_id = ? ORDER BY created_at DESC LIMIT 50",
            [$this->currentUser['id']]
        );
        $this->view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(string $id): void {
        Database::update('notifications', ['read_at' => now()], 'id = ? AND notifiable_id = ?', [$id, $this->currentUser['id']]);
        $this->json(['success' => true]);
    }

    public function markAllRead(): void {
        Database::query("UPDATE notifications SET read_at = NOW() WHERE notifiable_id = ? AND read_at IS NULL", [$this->currentUser['id']]);
        $this->json(['success' => true]);
    }
}
