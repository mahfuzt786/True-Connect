-- ============================================================
-- Database Seeders - Default Data
-- ============================================================

USE `saas_ecommerce`;

-- Super Admin user (password: Admin@123456)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`, `email_verified_at`) VALUES
('Super Admin', 'admin@saas.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active', NOW());

-- Subscription Plans
INSERT INTO `plans` (`name`, `slug`, `description`, `price_monthly`, `price_yearly`, `trial_days`, `products_limit`, `vendors_limit`, `storage_limit_mb`, `orders_limit`, `staff_limit`, `custom_domain`, `analytics`, `api_access`, `marketplace_enabled`, `priority_support`, `sort_order`, `features`) VALUES
('Starter', 'starter', 'Perfect for small stores getting started', 9.99, 99.00, 14, 50, NULL, 512, 500, 1, 0, 0, 0, 0, 0, 1, '["50 products","500 orders/month","512MB storage","Email support","Basic analytics"]'),
('Pro', 'pro', 'For growing businesses that need more power', 29.99, 299.00, 14, 500, NULL, 5120, NULL, 5, 1, 1, 1, 0, 0, 2, '["500 products","Unlimited orders","5GB storage","Priority support","Advanced analytics","Custom domain","API access"]'),
('Business', 'business', 'Full marketplace + e-commerce features', 79.99, 799.00, 14, NULL, 50, 20480, NULL, 20, 1, 1, 1, 1, 1, 3, '["Unlimited products","Unlimited orders","20GB storage","Dedicated support","Full analytics","Custom domain","API access","Marketplace with 50 vendors","Staff accounts"]'),
('Enterprise', 'enterprise', 'Custom solutions for large enterprises', 199.99, 1999.00, 30, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 4, '["Everything unlimited","Custom integrations","SLA guarantee","Account manager","White-label option"]'),
('Custom', 'custom', 'Free, super-admin managed plan. No subscription fee on initial setup; admin can edit limits and fees later.', 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 99, '["No subscription fee on setup","All features unlimited","Edit limits & fees anytime","Super-admin managed"]');

-- Global Settings
INSERT INTO `settings` (`store_id`, `group`, `key`, `value`, `type`, `is_public`) VALUES
(NULL, 'general', 'app_name', 'True Commerce', 'string', 1),
(NULL, 'general', 'app_url', 'http://localhost', 'string', 1),
(NULL, 'general', 'app_email', 'noreply@truecommerce.in', 'string', 0),
(NULL, 'general', 'app_currency', 'INR', 'string', 1),
(NULL, 'general', 'default_language', 'en', 'string', 1),
(NULL, 'general', 'default_timezone', 'Asia/Kolkata', 'string', 1),
(NULL, 'general', 'maintenance_mode', '0', 'boolean', 0),
(NULL, 'general', 'registration_enabled', '1', 'boolean', 1),
(NULL, 'mail', 'mail_driver', 'smtp', 'string', 0),
(NULL, 'mail', 'mail_host', 'smtp.mailtrap.io', 'string', 0),
(NULL, 'mail', 'mail_port', '587', 'integer', 0),
(NULL, 'mail', 'mail_username', '', 'string', 0),
(NULL, 'mail', 'mail_password', '', 'string', 0),
(NULL, 'mail', 'mail_from_name', 'True Commerce', 'string', 0),
(NULL, 'payment', 'stripe_enabled', '0', 'boolean', 0),
(NULL, 'payment', 'razorpay_enabled', '0', 'boolean', 0),
(NULL, 'payment', 'paypal_enabled', '0', 'boolean', 0),
(NULL, 'social', 'social_links', '{"facebook":"https://www.facebook.com/truecircle","twitter":"https://x.com/truecircle","instagram":"https://www.instagram.com/truecircle","linkedin":"https://www.linkedin.com/company/truecircle","youtube":"https://www.youtube.com/@truecircle"}', 'json', 1);
