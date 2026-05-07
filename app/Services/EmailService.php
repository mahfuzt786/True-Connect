<?php

class EmailService {

    public function sendEmailVerification(array $user): bool {
        $link = url("/email/verify/{$user['email_verify_token']}");
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject('Verify your email')
            ->template('email-verification', ['user' => $user, 'link' => $link])
            ->send();
    }

    public function sendPasswordReset(array $user, string $token): bool {
        $link = url("/reset-password/{$token}");
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject('Reset your password')
            ->template('password-reset', ['user' => $user, 'link' => $link])
            ->send();
    }

    public function sendOrderConfirmation(array $user, array $order): bool {
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject("Order Confirmation #{$order['order_number']}")
            ->template('order-confirmation', ['user' => $user, 'order' => $order])
            ->send();
    }

    public function sendOrderStatusUpdate(array $user, array $order, string $status, string $note = ''): bool {
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject("Order #{$order['order_number']} - " . ucfirst(str_replace('_', ' ', $status)))
            ->template('order-status', ['user' => $user, 'order' => $order, 'status' => $status, 'note' => $note])
            ->send();
    }

    public function sendVendorApproved(array $user, array $vendor): bool {
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject('Welcome - Your vendor application is approved!')
            ->template('vendor-approved', ['user' => $user, 'vendor' => $vendor])
            ->send();
    }

    public function sendSubscriptionRenewed(array $user, array $subscription): bool {
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject('Subscription Renewed Successfully')
            ->template('subscription-renewed', ['user' => $user, 'subscription' => $subscription])
            ->send();
    }

    public function sendSubscriptionCancelled(array $user, array $subscription): bool {
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject('Subscription Cancelled')
            ->template('subscription-cancelled', ['user' => $user, 'subscription' => $subscription])
            ->send();
    }

    public function sendTrialExpiringSoon(array $user, array $subscription, int $daysLeft): bool {
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject("Your trial ends in $daysLeft days")
            ->template('trial-expiring', ['user' => $user, 'subscription' => $subscription, 'daysLeft' => $daysLeft])
            ->send();
    }

    public function sendLowStockAlert(array $store, array $product): bool {
        $owner = Database::fetch("SELECT * FROM users WHERE id = ?", [$store['user_id']]);
        return Mailer::make()
            ->to($owner['email'], $owner['name'])
            ->subject("Low stock alert: {$product['name']}")
            ->template('low-stock', ['product' => $product, 'store' => $store])
            ->send();
    }

    public function sendNewOrderNotification(array $store, array $order): bool {
        $owner = Database::fetch("SELECT * FROM users WHERE id = ?", [$store['user_id']]);
        return Mailer::make()
            ->to($owner['email'], $owner['name'])
            ->subject("New order #{$order['order_number']}")
            ->template('new-order-admin', ['order' => $order, 'store' => $store])
            ->send();
    }

    public function sendInvoice(array $user, array $invoice, string $pdfPath): bool {
        return Mailer::make()
            ->to($user['email'], $user['name'])
            ->subject("Invoice #{$invoice['invoice_number']}")
            ->template('invoice-email', ['user' => $user, 'invoice' => $invoice])
            ->attach($pdfPath)
            ->send();
    }
}
