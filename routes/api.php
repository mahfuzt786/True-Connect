<?php
/** @var Router $router */

// ============================================================
// API ROUTES (v1) — all return JSON
// ============================================================

$router->group(['prefix' => '/api/v1', 'middleware' => ['ApiMiddleware']], function(Router $r) {

    // Auth
    $r->post('/auth/register', 'Api\AuthApiController@register');
    $r->post('/auth/login', 'Api\AuthApiController@login');
    $r->post('/auth/logout', 'Api\AuthApiController@logout');
    $r->post('/auth/refresh', 'Api\AuthApiController@refresh');
    $r->get('/auth/me', 'Api\AuthApiController@me');
    $r->post('/auth/forgot-password', 'Api\AuthApiController@forgotPassword');
    $r->post('/auth/reset-password', 'Api\AuthApiController@resetPassword');

    // Stores (public)
    $r->get('/stores/{slug}', 'Api\StoreApiController@show');
    $r->get('/stores/{slug}/config', 'Api\StoreApiController@config');

    // Products (public)
    $r->get('/stores/{storeSlug}/products', 'Api\ProductApiController@index');
    $r->get('/stores/{storeSlug}/products/{slug}', 'Api\ProductApiController@show');
    $r->get('/stores/{storeSlug}/categories', 'Api\ProductApiController@categories');
    $r->get('/stores/{storeSlug}/search', 'Api\ProductApiController@search');
    $r->get('/stores/{storeSlug}/featured', 'Api\ProductApiController@featured');

    // Cart (session-based for guests, user-based for auth)
    $r->get('/stores/{storeSlug}/cart', 'Api\CartApiController@show');
    $r->post('/stores/{storeSlug}/cart/add', 'Api\CartApiController@add');
    $r->put('/stores/{storeSlug}/cart/item/{id}', 'Api\CartApiController@update');
    $r->delete('/stores/{storeSlug}/cart/item/{id}', 'Api\CartApiController@remove');
    $r->post('/stores/{storeSlug}/cart/coupon', 'Api\CartApiController@applyCoupon');
    $r->delete('/stores/{storeSlug}/cart/coupon', 'Api\CartApiController@removeCoupon');

    // Checkout
    $r->post('/stores/{storeSlug}/checkout', 'Api\CheckoutApiController@process');
    $r->post('/stores/{storeSlug}/checkout/shipping-rates', 'Api\CheckoutApiController@shippingRates');

    // Orders (authenticated)
    $r->get('/orders', 'Api\OrderApiController@index');
    $r->get('/orders/{id}', 'Api\OrderApiController@show');
    $r->get('/orders/{id}/track', 'Api\OrderApiController@track');
    $r->post('/orders/{id}/cancel', 'Api\OrderApiController@cancel');

    // Reviews (authenticated)
    $r->get('/products/{id}/reviews', 'Api\ReviewApiController@index');
    $r->post('/products/{id}/reviews', 'Api\ReviewApiController@store');
    $r->put('/reviews/{id}', 'Api\ReviewApiController@update');
    $r->delete('/reviews/{id}', 'Api\ReviewApiController@destroy');

    // Wishlist (authenticated)
    $r->get('/wishlist', 'Api\WishlistApiController@index');
    $r->post('/wishlist', 'Api\WishlistApiController@toggle');

    // User Profile (authenticated)
    $r->get('/profile', 'Api\ProfileApiController@show');
    $r->put('/profile', 'Api\ProfileApiController@update');
    $r->post('/profile/avatar', 'Api\ProfileApiController@uploadAvatar');
    $r->put('/profile/password', 'Api\ProfileApiController@changePassword');
    $r->get('/profile/addresses', 'Api\ProfileApiController@addresses');
    $r->post('/profile/addresses', 'Api\ProfileApiController@storeAddress');
    $r->put('/profile/addresses/{id}', 'Api\ProfileApiController@updateAddress');
    $r->delete('/profile/addresses/{id}', 'Api\ProfileApiController@deleteAddress');

    // Notifications (authenticated)
    $r->get('/notifications', 'Api\NotificationApiController@index');
    $r->post('/notifications/{id}/read', 'Api\NotificationApiController@read');
    $r->post('/notifications/read-all', 'Api\NotificationApiController@readAll');

    // Vendor API (vendor auth)
    $r->get('/vendor/profile', 'Api\VendorApiController@profile');
    $r->put('/vendor/profile', 'Api\VendorApiController@updateProfile');
    $r->get('/vendor/products', 'Api\VendorApiController@products');
    $r->post('/vendor/products', 'Api\VendorApiController@storeProduct');
    $r->put('/vendor/products/{id}', 'Api\VendorApiController@updateProduct');
    $r->delete('/vendor/products/{id}', 'Api\VendorApiController@destroyProduct');
    $r->get('/vendor/orders', 'Api\VendorApiController@orders');
    $r->get('/vendor/earnings', 'Api\VendorApiController@earnings');

    // Subscriptions (store admin)
    $r->get('/subscription', 'Api\SubscriptionApiController@current');
    $r->get('/subscription/plans', 'Api\SubscriptionApiController@plans');
    $r->post('/subscription/subscribe', 'Api\SubscriptionApiController@subscribe');
    $r->post('/subscription/cancel', 'Api\SubscriptionApiController@cancel');

    // Store Admin API
    $r->get('/store/dashboard', 'Api\StoreAdminApiController@dashboard');
    $r->get('/store/analytics', 'Api\StoreAdminApiController@analytics');
    $r->get('/store/customers', 'Api\StoreAdminApiController@customers');
    $r->get('/store/reports', 'Api\StoreAdminApiController@reports');

    // Payments
    $r->post('/payment/intent', 'Api\PaymentApiController@createIntent');
    $r->post('/payment/confirm', 'Api\PaymentApiController@confirm');

    // API Tokens
    $r->get('/tokens', 'Api\TokenApiController@index');
    $r->post('/tokens', 'Api\TokenApiController@create');
    $r->delete('/tokens/{id}', 'Api\TokenApiController@destroy');
});
