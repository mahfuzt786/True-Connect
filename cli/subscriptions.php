<?php
// CLI: process recurring subscriptions, expire trials, send notifications
// Usage: php cli/subscriptions.php
require_once dirname(__DIR__) . '/core/bootstrap.php';

(new SubscriptionService())->processRecurring();
echo "Recurring subscriptions processed.\n";

// Notify trial-ending users (3 days before)
$expiring = Database::fetchAll(
    "SELECT sub.*, s.user_id FROM subscriptions sub
     JOIN stores s ON s.id = sub.store_id
     WHERE sub.status = 'trialing'
     AND sub.trial_ends_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)"
);
foreach ($expiring as $sub) {
    $daysLeft = max(1, (int)((strtotime($sub['trial_ends_at']) - time()) / 86400));
    $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$sub['user_id']]);
    if ($user) {
        try {
            (new EmailService())->sendTrialExpiringSoon($user, $sub, $daysLeft);
            echo "Notified {$user['email']} - $daysLeft days left\n";
        } catch (Throwable $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

// Low stock alerts
$lowStock = Database::fetchAll(
    "SELECT p.*, s.user_id FROM products p
     JOIN stores s ON s.id = p.store_id
     WHERE p.track_inventory = 1 AND p.quantity <= p.low_stock_threshold AND p.status = 'active'"
);
foreach ($lowStock as $p) {
    $store = Database::fetch("SELECT * FROM stores WHERE id = ?", [$p['store_id']]);
    try {
        (new EmailService())->sendLowStockAlert($store, $p);
    } catch (Throwable) {}
}
echo "Done.\n";
