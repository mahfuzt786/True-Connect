# SaaS E-commerce + Marketplace CMS

A complete subscription-based SaaS platform built with **Core PHP (MVC)** and **MySQL**. Users can register, choose a plan, and create either a single-vendor e-commerce store or a multi-vendor marketplace.

## Features

### Authentication
- Register / Login / Logout (session + JWT)
- Email verification
- Password reset
- Two-factor authentication (2FA / TOTP)
- Social login (Google, Facebook)
- Role-based access (Super Admin, Store Owner, Vendor, Customer)
- API tokens

### Store / Multi-Tenant
- Create unlimited stores
- Choose store type (e-commerce or marketplace)
- Subdomain + custom domain support
- 3 customizable themes (Default, Modern, Minimal)
- Theme color/font customization
- Logo, favicon, banner, social links
- SEO meta + Google Analytics + Facebook Pixel
- Staff accounts with permissions

### Subscription / Billing
- Plans (Starter / Pro / Business / Enterprise)
- Plan-based feature gating (products, vendors, storage, API)
- Free trials
- Monthly / yearly billing
- Stripe + Razorpay + PayPal subscription support
- Auto-renewal + invoicing
- Trial-expiring notifications
- Cancel / pause / reactivate

### Products
- CRUD with variants (size, color, custom attributes)
- Multiple images per product, drag-and-drop primary
- Categories & subcategories
- SKU management
- Inventory tracking + low-stock alerts
- Bulk import via CSV
- Bulk export to CSV
- Bulk actions (activate, deactivate, feature, delete)
- Featured products + tags
- Digital products
- Inventory log

### Cart & Checkout
- Session-based cart for guests, persistent for logged-in users
- Guest checkout
- Address book
- Coupon system (percentage, fixed, free shipping, BXGY)
- Tax calculation (zone-based)
- Shipping zones + rates (flat / weight / price / free)
- Order notes
- Apply coupon / remove coupon

### Orders
- Full order lifecycle (Pending → Confirmed → Processing → Shipped → Delivered)
- Order status history with notifications
- Tracking number + URL
- Refund handling (partial/full) with gateway integration
- PDF invoice generation
- Order export to CSV

### Payments
- **Stripe** (Checkout sessions + subscriptions)
- **Razorpay** (orders + signature verification)
- **PayPal** (orders + capture)
- **Cash on Delivery** (COD)
- **Bank Transfer**
- Webhook handlers for all gateways

### Marketplace (Multi-Vendor)
- Vendor registration & approval workflow
- Vendor dashboard (products, orders, earnings, settings)
- Configurable commission rates (per store or per vendor)
- Vendor payouts (bank, PayPal, manual)
- Vendor balance tracking
- Auto-approve vendors option

### Reviews & Ratings
- Product reviews with ratings (1-5 stars)
- Verified purchase badge
- Moderation (approve/reject/reply)
- Helpful votes
- Aggregate product ratings

### Admin Panels
- **Super Admin** dashboard: all stores, users, MRR, plans, audit log, email logs, jobs queue, platform settings
- **Store Admin** dashboard: orders, products, customers, analytics, vendor management
- **Vendor** dashboard: products, orders, earnings, settings

### Search & Filters
- Full-text product search
- Filter by category, price range, sort by (newest, price, rating, popularity)

### Wishlist
- Toggle wishlist (logged-in users)
- View wishlist with quick actions

### Notifications
- In-app notifications
- Email notifications (verify, reset, order, vendor approval, subscription, low-stock, trial expiring)
- Configurable mail driver (SMTP, log, native)

### Analytics
- Real-time KPIs (revenue, orders, customers, AOV)
- 30-day revenue chart
- Top products / best sellers
- Order status distribution
- Customer analytics (new vs returning, top spenders)
- Traffic analytics (page views, devices, sources)
- Sales reports with date range
- Export to CSV

### REST API (v1)
- JWT + token-based auth
- Auth: register/login/logout/refresh/me/forgot/reset
- Products + categories + search
- Cart + checkout
- Orders + tracking
- Reviews + wishlist
- Profile + addresses
- Notifications
- Vendor self-service
- Subscription management
- Store admin dashboard
- Token management

### Security
- CSRF token validation
- XSS protection (htmlspecialchars, CSP headers)
- SQL injection prevention (PDO prepared statements)
- Password hashing (bcrypt cost 12)
- Encrypted gateway credentials
- Rate limiting (login, registration, API)
- Session regeneration
- Remember-me with hashed tokens
- Security headers (X-Frame, X-XSS, X-Content-Type, HSTS, CSP)
- File upload validation (type, size, executable check)
- Audit log for all admin actions
- 2FA TOTP

### Other
- Multi-currency
- Multi-language support
- Multi-timezone
- Sitemap.xml + robots.txt
- Pages CMS (about, terms, FAQ)
- Media library
- Webhooks (incoming + outgoing)
- Queue jobs + failed jobs management
- Caching (file + Redis)

## Folder Structure

```
/app
  /Controllers       — All web + API controllers
    /Api             — REST API controllers
  /Models            — Data models
  /Services          — Business logic (cart, checkout, payments, email, etc.)
  /Middleware        — Auth, store, admin, vendor, API middleware
  /Views             — All templates (admin, account, vendor, superadmin, emails, errors)
/config              — Config files (app, database, mail, payment)
/core                — Framework: Router, Controller, Model, DB, Auth, JWT, CSRF, Session, Validator, Cache, Mailer, Upload, RateLimit
/database            — migrations.sql + seeders.sql
/public              — Web entry point (index.php), css/js assets, .htaccess
/routes              — web.php + api.php
/storage             — uploads, cache, logs, invoices
/themes              — Default, Modern, Minimal storefront themes
/install             — Install wizard
```

## Installation

### Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache (with mod_rewrite) or Nginx
- Extensions: PDO, OpenSSL, Mbstring, JSON, GD, Curl

### Quick Setup

1. **Clone / extract the project** into your web root:
   ```
   D:/xampp_8.1/htdocs/mahfuz/crm/
   ```

2. **Configure environment**: copy `.env.example` to `.env` and update with your DB credentials:
   ```env
   DB_HOST=127.0.0.1
   DB_DATABASE=saas_ecommerce
   DB_USERNAME=root
   DB_PASSWORD=
   APP_URL=http://localhost/mahfuz/crm/public
   ```

3. **Run the install wizard** by visiting:
   ```
   http://localhost/mahfuz/crm/public/install
   ```
   The wizard will:
   - Verify system requirements
   - Test database connection
   - Run migrations + seed default plans
   - Create your super admin account

4. **Or install manually**: import `/database/migrations.sql` and `/database/seeders.sql` via phpMyAdmin or CLI:
   ```bash
   mysql -u root -p < database/migrations.sql
   mysql -u root -p < database/seeders.sql
   ```

5. **Visit the app**:
   - Storefront: `http://localhost/mahfuz/crm/public`
   - Admin: `http://localhost/mahfuz/crm/public/admin`
   - Default super admin: `admin@saas.com` / `Admin@123456`

### Production Deployment

1. Set `APP_DEBUG=false` and `APP_ENV=production` in `.env`
2. Generate secure keys: `APP_KEY` and `JWT_SECRET`
3. Configure SMTP for emails (`MAIL_DRIVER=smtp`)
4. Add real payment gateway credentials
5. Point web root to `/public/` directory only
6. Enable HTTPS (uncomment HTTPS redirect in `public/.htaccess`)
7. Set up cron for queue + subscription processing:
   ```cron
   * * * * * cd /path/to/app && php cli/queue.php >> storage/logs/queue.log 2>&1
   0 * * * * cd /path/to/app && php cli/subscriptions.php
   ```

## API Quick Start

### Register & login
```bash
curl -X POST http://localhost/mahfuz/crm/public/api/v1/auth/register \
  -d "name=John&email=john@example.com&password=Pass1234&password_confirmation=Pass1234"

curl -X POST http://localhost/mahfuz/crm/public/api/v1/auth/login \
  -d "email=john@example.com&password=Pass1234"
# Returns { token, api_token, user }
```

### List products of a store
```bash
curl http://localhost/mahfuz/crm/public/api/v1/stores/my-store/products
```

### Add to cart + checkout
```bash
curl -X POST http://localhost/mahfuz/crm/public/api/v1/stores/my-store/cart/add \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "product_id=1&quantity=2"

curl -X POST http://localhost/mahfuz/crm/public/api/v1/stores/my-store/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "billing_first_name=John&billing_email=..."
```

## Tech Stack

**Backend:**
- Core PHP 8.0+ (MVC)
- MySQL / MariaDB (InnoDB)
- PDO with prepared statements
- Custom router, controller, model, validator, JWT, CSRF, cache, mailer

**Frontend:**
- Bootstrap 5
- Bootstrap Icons
- Chart.js (analytics)
- Vanilla JavaScript (no framework)
- 3 storefront themes

## License

MIT — Free for personal and commercial use.
