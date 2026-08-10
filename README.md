# The Wordfare Christian Assembly (TWCA Church) — Laravel Web Application

A comprehensive, production-ready web application and management system built for **The Wordfare Christian Assembly**. It features full content management (Devotionals, Sermons, Songs, Bookstore, Events), Church Administration (Members, Attendance, Staff, Financials), Cloudflare R2 storage integration, an automated Newsletter & Tracking system, and an integrated Maintenance Mode.

---

## 🌟 Key Features & Systems

### 1. **Public Website & Frontend**

- **Dynamic Homepage:** Custom Hero Showcase with background video/images, animated mesh background, and multi-line titles.
- **Teaching & Audio Streaming:** Sermon library with audio track playlists, search, and filtering by year/month.
- **Songs & Bookstore:** Integrated music media player and Christian bookstore catalog.
- **Daily Devotionals:** Devotional view with automatic shareable card/image generator.
- **Partnership & Giving:** Designated giving funds and bank transfer account listings.
- **Online Member Registration:** Guest member registration form with automated center assignment.
- **Wordfare Radio:** Live web radio player with real-time stream state tracking.

### 2. **Admin Management Panel (`/admin`)**

- **Role & Permissions:** Fine-grained module permission system (`view_dashboard`, `manage_sections`, `view_pages`, etc.).
- **Church Operations:** Manage Church Members, Attendance Tracking, Staff Records, and Financial Accounts/Transactions.
- **Site Settings:** Configurable site information, typography, colors, logo/favicon uploads, and Mail settings (SMTP / Resend API).
- **Maintenance Mode Toggle:** Built-in toggle switch inside Site Settings with custom titles, messages, app favicon integration, and admin route bypass.

### 3. **Cloudflare R2 Integration**

- Out-of-the-box integration with Cloudflare R2 bucket storage (S3-compatible).
- **All uploads go directly to R2**: sermons, songs, book PDFs, covers (`sermon-covers/`, `songs-covers/`, `book-covers/`), hero backgrounds (`heros/`), newsletter images (`newsletter-images/`), and brand images — logo, favicon, devotional header logo (`logos/`).
- Files are served via a custom CDN domain (`R2_PUBLIC_URL`, e.g. `https://cdn.twcaglobal.org`), keeping local disk usage minimal.

### 4. **Newsletter Campaign & Analytics Engine**

- **Instant Activation:** Single-click active subscriptions (no manual approval required).
- **Email Tracking:** Automatic 1x1 open tracking pixel injection and link wrapping for click analytics with unique-open deduplication.
- **Public Domain Configuration:** Configurable tracking URL support via `NEWSLETTER_PUBLIC_URL`.

---

## 🚀 Production Deployment & Launch Guide

> **The authoritative, step-by-step deployment guide is [`DEPLOY.md`](DEPLOY.md).**
> It covers the live environment specifics (cPanel document root, PHP extensions,
> R2 credentials, Resend mail, scheduler & queue crons, security cleanup).

### 1. Environment Configuration (`.env`)

Use `.env.example` as the template. Key production values (see `DEPLOY.md` for the full list):

```env
APP_NAME="TWCA Church"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://twcaglobal.org

# Database — utf8 (NOT utf8mb4) for compatibility with older MySQL servers
DB_CONNECTION=mysql
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password
DB_CHARSET=utf8
DB_COLLATION=utf8_unicode_ci

# Cloudflare R2 — custom CDN domain attached to the bucket (NOT the pub-xxx.r2.dev URL)
R2_ACCOUNT_ID=your_account_id
R2_ACCESS_KEY=your_access_key
R2_SECRET_KEY=your_secret_key
R2_BUCKET_NAME=twca
R2_PUBLIC_URL=https://cdn.twcaglobal.org

# Mail — Resend API (see DEPLOY.md for SMTP alternative)
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="no-reply@twcaglobal.org"
MAIL_FROM_NAME="${APP_NAME}"
RESEND_API_KEY=re_xxxxxxxx

# Newsletter tracking domain + webhook secret
NEWSLETTER_PUBLIC_URL=https://twcaglobal.org
NEWSLETTER_WEBHOOK_SECRET=<long random hex>
```

### 2. Production Optimization Commands

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Queue Worker (Emails & Newsletter Campaigns)

Emails are queued via the database driver (`QUEUE_CONNECTION=database`).
On a VPS with Supervisor, use:

```bash
php artisan queue:work --tries=3 --timeout=90
```

On **cPanel (no supervisor)**, run the worker via cron — see `DEPLOY.md`.

### 4. Scheduled Cron Job (Automated Tasks)

Add the Laravel Scheduler so scheduled newsletters run:

```cron
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

### 5. File System Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

---

## 🛠 Tech Stack

- **Framework:** Laravel 13.x / PHP 8.3
- **Frontend Styling:** Custom CSS, Bootstrap 4, FontAwesome 6, Owl Carousel, Poppins Typography
- **Database:** MySQL
- **Storage:** Cloudflare R2 (S3-compatible Object Storage)
- **Email & Delivery:** Laravel Mail, Resend API / SMTP, Custom Open & Click Analytics

---

## 📄 License

This application is proprietary software developed for **The Wordfare Christian Assembly**. All rights reserved.
