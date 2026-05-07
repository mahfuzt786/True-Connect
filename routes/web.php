<?php
/** @var Router $router */

// ============================================================
// WEB ROUTES
// ============================================================

// ---- Public / Homepage ----
$router->get('/', 'HomeController@index', 'home');
$router->get('/pricing', 'HomeController@pricing', 'pricing');
$router->get('/features', 'HomeController@features', 'features');
$router->get('/about', 'HomeController@about', 'about');
$router->get('/contact', 'HomeController@contact', 'contact.show');
$router->post('/contact', 'HomeController@contactSubmit', 'contact.submit');
$router->get('/robots.txt', 'HomeController@robots');
$router->get('/sitemap.xml', 'HomeController@sitemap');

// ---- Public Marketplace ----
$router->get('/marketplace', 'MarketplaceController@index', 'marketplace');
$router->get('/marketplace/products', 'MarketplaceController@products', 'marketplace.products');
$router->get('/marketplace/products/{id}', 'MarketplaceController@product', 'marketplace.product');
$router->get('/marketplace/stores', 'MarketplaceController@stores', 'marketplace.stores');

// ---- Auth Routes ----
$router->group(['prefix' => ''], function(Router $r) {
    $r->get('/register', 'AuthController@showRegister', 'register');
    $r->post('/register', 'AuthController@register');
    $r->get('/sell/register', 'AuthController@showSellerRegister', 'seller.register');
    $r->post('/sell/register', 'AuthController@sellerRegister');
    $r->get('/login', 'AuthController@showLogin', 'login');
    $r->post('/login', 'AuthController@login');
    $r->post('/logout', 'AuthController@logout', 'logout');
    $r->get('/email/verify/{token}', 'AuthController@verifyEmail', 'email.verify');
    $r->post('/email/resend', 'AuthController@resendVerification', 'email.resend');
    $r->get('/forgot-password', 'AuthController@showForgotPassword', 'password.forgot');
    $r->post('/forgot-password', 'AuthController@sendResetLink', 'password.email');
    $r->get('/reset-password/{token}', 'AuthController@showResetPassword', 'password.reset');
    $r->post('/reset-password', 'AuthController@resetPassword', 'password.update');
    $r->get('/auth/google', 'AuthController@googleRedirect', 'auth.google');
    $r->get('/auth/google/callback', 'AuthController@googleCallback');
    $r->get('/auth/facebook', 'AuthController@facebookRedirect', 'auth.facebook');
    $r->get('/auth/facebook/callback', 'AuthController@facebookCallback');
});

// ---- Dashboard & Store Owner ----
$router->group(['prefix' => '/dashboard', 'middleware' => ['AuthMiddleware']], function(Router $r) {
    $r->get('', 'DashboardController@index', 'dashboard');
    $r->get('/analytics', 'DashboardController@analytics', 'dashboard.analytics');
    $r->get('/reports/sales', 'DashboardController@salesReport', 'reports.sales');
    $r->get('/reports/export', 'DashboardController@exportReport', 'reports.export');

    // Profile
    $r->get('/profile', 'ProfileController@show', 'profile');
    $r->post('/profile', 'ProfileController@update');
    $r->post('/profile/password', 'ProfileController@changePassword', 'profile.password');
    $r->post('/profile/avatar', 'ProfileController@uploadAvatar', 'profile.avatar');
    $r->post('/profile/2fa/enable', 'ProfileController@enable2FA', 'profile.2fa.enable');
    $r->post('/profile/2fa/disable', 'ProfileController@disable2FA', 'profile.2fa.disable');
    $r->post('/profile/2fa/verify', 'ProfileController@verify2FA');

    // Notifications
    $r->get('/notifications', 'NotificationController@index', 'notifications');
    $r->post('/notifications/{id}/read', 'NotificationController@markRead');
    $r->post('/notifications/read-all', 'NotificationController@markAllRead');
});

// ---- Store Setup (Onboarding) ----
$router->group(['prefix' => '/store', 'middleware' => ['AuthMiddleware']], function(Router $r) {
    $r->get('/setup', 'StoreController@setup', 'store.setup');
    $r->post('/setup', 'StoreController@storeSetup');
    $r->get('/setup/theme', 'StoreController@themeSetup', 'store.theme');
    $r->post('/setup/theme', 'StoreController@saveTheme');

    // Store Settings
    $r->get('/settings', 'StoreController@settings', 'store.settings');
    $r->post('/settings/general', 'StoreController@updateGeneral');
    $r->post('/settings/appearance', 'StoreController@updateAppearance');
    $r->post('/settings/domain', 'StoreController@updateDomain');
    $r->post('/settings/seo', 'StoreController@updateSEO');
    $r->post('/settings/social', 'StoreController@updateSocial');

    // Staff
    $r->get('/staff', 'StoreController@staff', 'store.staff');
    $r->post('/staff/invite', 'StoreController@inviteStaff');
    $r->delete('/staff/{id}', 'StoreController@removeStaff');

    // Shipping
    $r->get('/shipping', 'ShippingController@index', 'shipping');
    $r->post('/shipping/zone', 'ShippingController@createZone');
    $r->post('/shipping/zone/{id}', 'ShippingController@updateZone');
    $r->delete('/shipping/zone/{id}', 'ShippingController@deleteZone');
    $r->post('/shipping/rate', 'ShippingController@createRate');
    $r->delete('/shipping/rate/{id}', 'ShippingController@deleteRate');

    // Tax
    $r->get('/tax', 'TaxController@index', 'tax');
    $r->post('/tax', 'TaxController@store');
    $r->put('/tax/{id}', 'TaxController@update');
    $r->delete('/tax/{id}', 'TaxController@destroy');

    // Payment Gateways
    $r->get('/payments', 'PaymentMethodController@index', 'payment.methods');
    $r->post('/payments/{gateway}', 'PaymentMethodController@update');
    $r->post('/payments/{gateway}/toggle', 'PaymentMethodController@toggle');

    // Pages
    $r->get('/pages', 'PageController@index', 'pages');
    $r->get('/pages/create', 'PageController@create');
    $r->post('/pages', 'PageController@store');
    $r->get('/pages/{id}/edit', 'PageController@edit');
    $r->post('/pages/{id}', 'PageController@update');
    $r->delete('/pages/{id}', 'PageController@destroy');

    // Media
    $r->get('/media', 'MediaController@index', 'media');
    $r->post('/media/upload', 'MediaController@upload');
    $r->delete('/media/{id}', 'MediaController@destroy');
});

// ---- Products ----
$router->group(['prefix' => '/products', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'ProductController@index', 'products');
    $r->get('/create', 'ProductController@create', 'products.create');
    $r->post('', 'ProductController@store', 'products.store');
    $r->get('/{id}/edit', 'ProductController@edit', 'products.edit');
    $r->post('/{id}', 'ProductController@update', 'products.update');
    $r->delete('/{id}', 'ProductController@destroy', 'products.destroy');
    $r->post('/{id}/duplicate', 'ProductController@duplicate');
    $r->post('/bulk', 'ProductController@bulk', 'products.bulk');
    $r->get('/import', 'ProductController@importForm', 'products.import');
    $r->post('/import', 'ProductController@import');
    $r->get('/export', 'ProductController@export', 'products.export');

    // Variants
    $r->post('/{id}/variants', 'ProductController@addVariant');
    $r->put('/{id}/variants/{vid}', 'ProductController@updateVariant');
    $r->delete('/{id}/variants/{vid}', 'ProductController@deleteVariant');

    // Images
    $r->post('/{id}/images', 'ProductController@uploadImages');
    $r->delete('/{id}/images/{imgId}', 'ProductController@deleteImage');
    $r->post('/{id}/images/{imgId}/primary', 'ProductController@setPrimaryImage');
});

// ---- Categories ----
$router->group(['prefix' => '/categories', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'CategoryController@index', 'categories');
    $r->post('', 'CategoryController@store', 'categories.store');
    $r->get('/{id}/edit', 'CategoryController@edit');
    $r->post('/{id}', 'CategoryController@update');
    $r->delete('/{id}', 'CategoryController@destroy');
    $r->post('/reorder', 'CategoryController@reorder');
});

// ---- Orders ----
$router->group(['prefix' => '/orders', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'OrderController@index', 'orders');
    $r->get('/create', 'OrderController@create', 'orders.create');
    $r->post('', 'OrderController@store');
    $r->get('/{id}', 'OrderController@show', 'orders.show');
    $r->post('/{id}/status', 'OrderController@updateStatus', 'orders.status');
    $r->post('/{id}/tracking', 'OrderController@updateTracking');
    $r->post('/{id}/refund', 'OrderController@createRefund', 'orders.refund');
    $r->get('/{id}/invoice', 'OrderController@invoice', 'orders.invoice');
    $r->get('/export', 'OrderController@export', 'orders.export');
});

// ---- Customers ----
$router->group(['prefix' => '/customers', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'CustomerController@index', 'customers');
    $r->get('/{id}', 'CustomerController@show', 'customers.show');
    $r->post('/{id}/ban', 'CustomerController@ban');
    $r->get('/export', 'CustomerController@export');
});

// ---- Coupons ----
$router->group(['prefix' => '/coupons', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'CouponController@index', 'coupons');
    $r->post('', 'CouponController@store');
    $r->get('/{id}/edit', 'CouponController@edit');
    $r->post('/{id}', 'CouponController@update');
    $r->delete('/{id}', 'CouponController@destroy');
});

// ---- Reviews ----
$router->group(['prefix' => '/reviews', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'ReviewController@index', 'reviews');
    $r->post('/{id}/approve', 'ReviewController@approve');
    $r->post('/{id}/reject', 'ReviewController@reject');
    $r->delete('/{id}', 'ReviewController@destroy');
    $r->post('/{id}/reply', 'ReviewController@reply');
});

// ---- Analytics / Reports ----
$router->group(['prefix' => '/analytics', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'AnalyticsController@index', 'analytics');
    $r->get('/revenue', 'AnalyticsController@revenue');
    $r->get('/products', 'AnalyticsController@products');
    $r->get('/customers', 'AnalyticsController@customers');
    $r->get('/traffic', 'AnalyticsController@traffic');
});

// ---- Vendors (Marketplace) ----
$router->group(['prefix' => '/vendors', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'VendorController@index', 'vendors');
    $r->get('/{id}', 'VendorController@show', 'vendors.show');
    $r->post('/{id}/approve', 'VendorController@approve');
    $r->post('/{id}/reject', 'VendorController@reject');
    $r->post('/{id}/suspend', 'VendorController@suspend');
    $r->post('/{id}/payout', 'VendorController@payout');
    $r->get('/payouts', 'VendorController@payouts', 'vendors.payouts');
});

// ---- Vendor Registration (any logged-in user can apply) ----
$router->group(['prefix' => '/vendor', 'middleware' => ['AuthMiddleware']], function(Router $r) {
    $r->get('/register', 'VendorDashboardController@registerForm', 'vendor.register');
    $r->post('/register', 'VendorDashboardController@register');
});

// ---- Vendor Dashboard (only approved vendors / store owners / admins) ----
$router->group(['prefix' => '/vendor', 'middleware' => ['AuthMiddleware', 'VendorMiddleware']], function(Router $r) {
    $r->get('/dashboard', 'VendorDashboardController@index', 'vendor.dashboard');
    $r->get('/products', 'VendorDashboardController@products', 'vendor.products');
    $r->get('/products/create', 'VendorDashboardController@createProduct');
    $r->post('/products', 'VendorDashboardController@storeProduct');
    $r->get('/products/{id}/edit', 'VendorDashboardController@editProduct');
    $r->post('/products/{id}', 'VendorDashboardController@updateProduct');
    $r->delete('/products/{id}', 'VendorDashboardController@destroyProduct');
    $r->get('/orders', 'VendorDashboardController@orders', 'vendor.orders');
    $r->get('/orders/{id}', 'VendorDashboardController@orderDetail');
    $r->get('/earnings', 'VendorDashboardController@earnings', 'vendor.earnings');
    $r->get('/settings', 'VendorDashboardController@settings', 'vendor.settings');
    $r->post('/settings', 'VendorDashboardController@updateSettings');
});

// ---- Subscriptions ----
$router->group(['prefix' => '/subscription', 'middleware' => ['AuthMiddleware']], function(Router $r) {
    $r->get('', 'SubscriptionController@index', 'subscription');
    $r->get('/plans', 'SubscriptionController@plans', 'subscription.plans');
    $r->post('/subscribe/{planId}', 'SubscriptionController@subscribe', 'subscription.subscribe');
    $r->post('/cancel', 'SubscriptionController@cancel', 'subscription.cancel');
    $r->get('/invoices', 'SubscriptionController@invoices', 'subscription.invoices');
    $r->get('/invoices/{id}/download', 'SubscriptionController@downloadInvoice');
    $r->post('/billing-portal', 'SubscriptionController@billingPortal');
});

// ---- Payment Webhooks ----
$router->post('/webhooks/stripe', 'WebhookController@stripe', 'webhooks.stripe');
$router->post('/webhooks/razorpay', 'WebhookController@razorpay', 'webhooks.razorpay');
$router->post('/webhooks/paypal', 'WebhookController@paypal', 'webhooks.paypal');

// ---- Theme Customizer (AJAX) ----
$router->group(['prefix' => '/theme', 'middleware' => ['AuthMiddleware', 'StoreMiddleware']], function(Router $r) {
    $r->get('', 'ThemeController@index', 'theme');
    $r->post('/switch', 'ThemeController@switch');
    $r->post('/customize', 'ThemeController@customize');
    $r->get('/preview/{theme}', 'ThemeController@preview');
});

// ---- Super Admin ----
$router->group(['prefix' => '/admin', 'middleware' => ['AuthMiddleware', 'AdminMiddleware']], function(Router $r) {
    $r->get('', 'AdminController@dashboard', 'admin.dashboard');
    $r->get('/stores', 'AdminController@stores', 'admin.stores');
    $r->get('/stores/create', 'AdminController@createStoreForm', 'admin.stores.create');
    $r->post('/stores', 'AdminController@createStore', 'admin.stores.store');
    $r->get('/stores/{id}', 'AdminController@storeDetail', 'admin.stores.show');
    $r->post('/stores/{id}/approve', 'AdminController@approveStore', 'admin.stores.approve');
    $r->post('/stores/{id}/reject',  'AdminController@rejectStore',  'admin.stores.reject');
    $r->post('/stores/{id}/suspend', 'AdminController@suspendStore');
    $r->post('/stores/{id}/activate', 'AdminController@activateStore');
    $r->post('/stores/{id}/impersonate', 'AdminController@impersonateStore', 'admin.stores.impersonate');
    $r->post('/stores/leave-impersonation', 'AdminController@leaveImpersonation', 'admin.stores.leave');
    $r->delete('/stores/{id}', 'AdminController@deleteStore');
    $r->get('/users', 'AdminController@users', 'admin.users');
    $r->get('/users/{id}', 'AdminController@userDetail');
    $r->get('/users/{id}/activity', 'AdminController@userActivity', 'admin.users.activity');
    $r->get('/stores/{id}/activity', 'AdminController@storeActivity', 'admin.stores.activity');
    $r->post('/users/{id}/approve', 'AdminController@approveUser', 'admin.users.approve');
    $r->post('/users/{id}/reject',  'AdminController@rejectUser',  'admin.users.reject');
    $r->post('/users/{id}/ban',     'AdminController@banUser',     'admin.users.ban');
    $r->post('/users/{id}/unban',   'AdminController@unbanUser',   'admin.users.unban');
    $r->get('/subscriptions', 'AdminController@subscriptions', 'admin.subscriptions');
    $r->get('/plans', 'AdminController@plans', 'admin.plans');
    $r->post('/plans', 'AdminController@createPlan');
    $r->put('/plans/{id}', 'AdminController@updatePlan');
    $r->delete('/plans/{id}', 'AdminController@deletePlan');
    $r->get('/revenue', 'AdminController@revenue', 'admin.revenue');
    $r->get('/analytics', 'AdminController@analytics', 'admin.analytics');
    $r->get('/settings', 'AdminController@settings', 'admin.settings');
    $r->post('/settings', 'AdminController@updateSettings');
    $r->get('/audit-log', 'AdminController@auditLogs', 'admin.audit');
    $r->get('/email-logs', 'AdminController@emailLogs', 'admin.email-logs');
    $r->get('/jobs', 'AdminController@jobs', 'admin.jobs');
    $r->post('/jobs/{id}/retry', 'AdminController@retryJob');
    $r->delete('/jobs/{id}', 'AdminController@deleteJob');
});

// ---- Storefront (Customer-facing) ----
$router->group(['prefix' => '/shop'], function(Router $r) {
    $r->get('/{storeSlug}', 'StorefrontController@home', 'storefront.home');
    $r->get('/{storeSlug}/products', 'StorefrontController@products', 'storefront.products');
    $r->get('/{storeSlug}/products/{slug}', 'StorefrontController@product', 'storefront.product');
    $r->get('/{storeSlug}/category/{slug}', 'StorefrontController@category', 'storefront.category');
    $r->get('/{storeSlug}/cart', 'StorefrontController@cart', 'storefront.cart');
    $r->post('/{storeSlug}/cart/add', 'StorefrontController@addToCart');
    $r->post('/{storeSlug}/cart/update', 'StorefrontController@updateCart');
    $r->post('/{storeSlug}/cart/remove', 'StorefrontController@removeFromCart');
    $r->get('/{storeSlug}/checkout', 'StorefrontController@checkout', 'storefront.checkout');
    $r->post('/{storeSlug}/checkout', 'StorefrontController@processCheckout');
    $r->get('/{storeSlug}/order/{orderNumber}/success', 'StorefrontController@orderSuccess');
    $r->get('/{storeSlug}/order/{orderNumber}/failed', 'StorefrontController@orderFailed');
    $r->post('/{storeSlug}/coupon/apply', 'StorefrontController@applyCoupon');
    $r->get('/{storeSlug}/search', 'StorefrontController@search', 'storefront.search');
    $r->get('/{storeSlug}/vendors', 'StorefrontController@vendors', 'storefront.vendors');
    $r->get('/{storeSlug}/vendors/{slug}', 'StorefrontController@vendorShop', 'storefront.vendor');
    $r->get('/{storeSlug}/pages/{slug}', 'StorefrontController@page', 'storefront.page');
    $r->post('/{storeSlug}/reviews', 'StorefrontController@submitReview', 'storefront.review');
    $r->post('/{storeSlug}/wishlist/toggle', 'StorefrontController@toggleWishlist');
    $r->get('/{storeSlug}/track/{orderNumber}', 'StorefrontController@trackOrder', 'storefront.track');
});

// ---- Customer Account ----
$router->group(['prefix' => '/account', 'middleware' => ['AuthMiddleware']], function(Router $r) {
    $r->get('', 'AccountController@index', 'account');
    $r->get('/orders', 'AccountController@orders', 'account.orders');
    $r->get('/orders/{id}', 'AccountController@orderDetail', 'account.order.show');
    $r->get('/wishlist', 'AccountController@wishlist', 'account.wishlist');
    $r->get('/addresses', 'AccountController@addresses', 'account.addresses');
    $r->post('/addresses', 'AccountController@storeAddress');
    $r->put('/addresses/{id}', 'AccountController@updateAddress');
    $r->delete('/addresses/{id}', 'AccountController@deleteAddress');
    $r->post('/addresses/{id}/default', 'AccountController@setDefaultAddress');
    $r->get('/reviews', 'AccountController@reviews', 'account.reviews');
    $r->get('/profile', 'AccountController@profile', 'account.profile');
    $r->post('/profile', 'AccountController@updateProfile');
    $r->post('/password', 'AccountController@changePassword');
});

// ---- Payment Callbacks ----
$router->get('/payment/stripe/success', 'PaymentController@stripeSuccess');
$router->get('/payment/stripe/cancel', 'PaymentController@stripeCancel');
$router->get('/payment/razorpay/success', 'PaymentController@razorpaySuccess');
$router->get('/payment/paypal/success', 'PaymentController@paypalSuccess');
$router->get('/payment/paypal/cancel', 'PaymentController@paypalCancel');

// ---- Sitemap & SEO ----
$router->get('/sitemap.xml', 'SeoController@sitemap', 'sitemap');
$router->get('/robots.txt', 'SeoController@robots', 'robots');

// ---- Install wizard ----
$router->get('/install', 'InstallController@index', 'install');
$router->post('/install/step/{step}', 'InstallController@step');
