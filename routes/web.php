<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ChurchController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DevotionalImageController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\NewsletterPublicController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend Routes — Public (no auth required)
|--------------------------------------------------------------------------
*/
Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::post('/contact', [FrontendController::class, 'contactStore'])->name('contact.store')->middleware('throttle:5,60');
Route::get('/devotionals/{slug}/share-image', [DevotionalImageController::class, 'generate'])
    ->name('devotionals.share-image')
    ->middleware('throttle:30,1');

Route::get('/teachings', [FrontendController::class, 'sermons'])->name('sermons');
Route::get('/teachings/{slug}', [FrontendController::class, 'sermonsShow'])->name('sermons.show');
Route::get('/songs', [FrontendController::class, 'songs'])->name('songs');
Route::get('/songs/{slug}', [FrontendController::class, 'songsShow'])->name('songs.show');
Route::get('/bookstore', [FrontendController::class, 'bookstore'])->name('bookstore');
Route::get('/bookstore/{slug}', [FrontendController::class, 'bookstoreShow'])->name('bookstore.show');
Route::get('/devotionals', [FrontendController::class, 'devotionals'])->name('devotionals');
Route::get('/devotionals/{slug}', [FrontendController::class, 'devotionalsShow'])->name('devotionals.show');
Route::get('/partnership-giving', [FrontendController::class, 'partnershipGiving'])->name('partnership.giving');

/*
|--------------------------------------------------------------------------
| Event Registration Routes (public)
|--------------------------------------------------------------------------
*/
Route::get('/events/{slug}', [FrontendController::class, 'eventShow'])->name('events.show');
Route::get('/events/{slug}/register', [FrontendController::class, 'eventRegister'])->name('events.register');
Route::post('/events/{slug}/register', [FrontendController::class, 'eventRegisterStore'])->name('events.register.store')->middleware('throttle:5,60');
Route::post('/events/member-lookup', [FrontendController::class, 'eventMemberLookup'])->name('events.member-lookup')->middleware('throttle:10,60');

/*
|--------------------------------------------------------------------------
| Member Registration Routes
|--------------------------------------------------------------------------
*/
Route::get('/member-reg', [FrontendController::class, 'memberReg'])->name('member.register');
Route::post('/member-reg', [FrontendController::class, 'memberRegStore'])->name('member.register.store')->middleware('throttle:5,60');

/*
|--------------------------------------------------------------------------
| Newsletter Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('newsletter')->name('newsletter.')->group(function () {
    Route::get('subscribe', [NewsletterPublicController::class, 'subscribeForm'])->name('subscribe');
    Route::post('subscribe', [NewsletterPublicController::class, 'subscribeStore'])->name('subscribe.store')->middleware('throttle:5,60');
    Route::get('verify/{token}', [NewsletterPublicController::class, 'verify'])->name('verify');
    Route::get('unsubscribe/{token}', [NewsletterPublicController::class, 'unsubscribeForm'])->name('unsubscribe');
    Route::post('unsubscribe/{token}', [NewsletterPublicController::class, 'unsubscribeProcess'])->name('unsubscribe.process');
});

/*
|--------------------------------------------------------------------------
| Newsletter Tracking Routes (no CSRF, no auth — accessed by email clients)
|--------------------------------------------------------------------------
*/
Route::get('newsletter/track-open/{newsletter}/{subscriber}', [NewsletterPublicController::class, 'trackOpen'])
    ->name('newsletter.track-open')
    ->middleware('throttle:60,1');
Route::get('newsletter/track-click', [NewsletterPublicController::class, 'trackClick'])
    ->name('newsletter.track-click')
    ->middleware('throttle:60,1');
Route::post('newsletter/webhook', [NewsletterPublicController::class, 'handleWebhook'])
    ->name('newsletter.webhook')
    ->withoutMiddleware(VerifyCsrfToken::class);

/*
|--------------------------------------------------------------------------
| SEO Routes (Sitemap & Robots.txt)
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [\App\Http\Controllers\FrontendController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\FrontendController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Payment Gateway Routes
|--------------------------------------------------------------------------
*/
Route::post('/payment/initialize', [\App\Http\Controllers\PaymentController::class, 'initializePayment'])->name('payment.initialize')->middleware('throttle:60,1');
Route::post('/payment/verify', [\App\Http\Controllers\PaymentController::class, 'verifyPayment'])->name('payment.verify')->middleware('throttle:60,1');
Route::post('/payment/webhook/{provider}', [\App\Http\Controllers\PaymentController::class, 'handleWebhook'])
    ->name('payment.webhook')
    ->withoutMiddleware(VerifyCsrfToken::class);

/*
|--------------------------------------------------------------------------
| Admin Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', function () {
        return view('admin.login');
    })->name('login');

    Route::post('login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:10,60');

    // Two-Factor Verification — pending login, no auth yet (with rate limiting)
    Route::get('two-factor', [TwoFactorController::class, 'showVerifyForm'])->name('two-factor.verify');
    Route::post('two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify.post')->middleware('throttle:10,15');
    Route::post('two-factor/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend')->middleware('throttle:5,10');

    // Forgot Password — no auth required (with rate limiting)
    Route::get('forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('forgot-password');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('forgot-password.send')->middleware('throttle:5,10');
    Route::get('verify-otp', [ForgotPasswordController::class, 'showVerifyOtpForm'])->name('forgot-password.verify-form');
    Route::post('verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('forgot-password.verify')->middleware('throttle:10,15');
    Route::get('reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('forgot-password.reset-form');
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('forgot-password.reset')->middleware('throttle:5,10');

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard & Profile (view_dashboard)
        Route::middleware('permission:view_dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            // Unnamed alias so /admin/dashboard keeps working (avoids duplicate route name)
            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
            Route::post('/profile', [DashboardController::class, 'profileUpdate'])->name('profile.update');
        });

        // Homepage (view_sections or manage_sections)
        Route::middleware('permission:view_sections,manage_sections')->group(function () {
            Route::match(['get', 'post'], '/hero', [HomepageController::class, 'heroPage'])->name('hero');
            Route::match(['get', 'post'], '/hero-section', [HomepageController::class, 'homepageSliders'])->name('hero-section');
            Route::match(['get', 'post'], '/hero-settings', [HomepageController::class, 'heroSettings'])->name('hero-settings');
            Route::match(['get', 'post'], '/ministry-columns', [HomepageController::class, 'ministryColumns'])->name('ministry-columns');
        });

        // About Us (view_pages or manage_pages)
        Route::match(['get', 'post'], '/about', [AboutController::class, 'about'])->name('about')->middleware('permission:view_pages,manage_pages');

        // Content Management
        Route::match(['get', 'post'], '/books', [ContentController::class, 'books'])->name('books')->middleware('permission:view_books,manage_books');
        Route::match(['get', 'post'], '/events', [ContentController::class, 'events'])->name('events')->middleware('permission:view_events,manage_events');
        Route::match(['get', 'post'], '/sermons', [ContentController::class, 'sermons'])->name('sermons')->middleware('permission:view_sermons,manage_sermons');
        Route::match(['get', 'post'], '/devotionals', [ContentController::class, 'devotionals'])->name('devotionals')->middleware('permission:view_devotionals,manage_devotionals');
        Route::match(['get', 'post'], '/songs', [ContentController::class, 'songs'])->name('songs')->middleware('permission:view_songs,manage_songs');
        Route::post('/media/upload', [ContentController::class, 'uploadMedia'])->name('media.upload')->middleware('permission:view_sermons,manage_sermons,view_songs,manage_songs,view_sections,manage_sections,view_books,manage_books');
        Route::match(['get', 'post'], '/quotes', [ContentController::class, 'quotes'])->name('quotes')->middleware('permission:view_quotes,manage_quotes');

        // Inbox (view_inbox or manage_inbox)
        Route::middleware('permission:view_inbox,manage_inbox')->group(function () {
            Route::match(['get', 'post'], '/inbox', [ContentController::class, 'inbox'])->name('inbox');
            Route::post('/inbox/mark-read', [ContentController::class, 'markAsRead'])->name('inbox.mark-read');
        });

        // Church Management
        Route::match(['get', 'post'], '/members', [ChurchController::class, 'members'])->name('members')->middleware('permission:view_members,manage_members');
        Route::get('/members/{id}', [ChurchController::class, 'memberView'])->name('members.view')->middleware('permission:view_members');
        Route::match(['get', 'post'], '/attendance', [ChurchController::class, 'attendance'])->name('attendance')->middleware('permission:view_attendance,manage_attendance');

        // Financials (view_offerings or manage_offerings)
        Route::match(['get', 'post'], '/financials', [FinancialController::class, 'index'])->name('financials')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/transaction', [FinancialController::class, 'storeTransaction'])->name('financials.transaction.store')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/transaction/{id}', [FinancialController::class, 'updateTransaction'])->name('financials.transaction.update')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/transaction/{id}/delete', [FinancialController::class, 'deleteTransaction'])->name('financials.transaction.delete')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/transaction/{id}/approve', [FinancialController::class, 'approveTransaction'])->name('financials.transaction.approve')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/transaction/{id}/reject', [FinancialController::class, 'rejectTransaction'])->name('financials.transaction.reject')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/account', [FinancialController::class, 'storeAccount'])->name('financials.account.store')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/account/{id}', [FinancialController::class, 'updateAccount'])->name('financials.account.update')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/account/{id}/delete', [FinancialController::class, 'deleteAccount'])->name('financials.account.delete')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/fund', [FinancialController::class, 'storeFund'])->name('financials.fund.store')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/fund/{id}', [FinancialController::class, 'updateFund'])->name('financials.fund.update')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/fund/{id}/delete', [FinancialController::class, 'deleteFund'])->name('financials.fund.delete')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/campaign', [FinancialController::class, 'storeCampaign'])->name('financials.campaign.store')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/campaign/{id}', [FinancialController::class, 'updateCampaign'])->name('financials.campaign.update')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/campaign/{id}/delete', [FinancialController::class, 'deleteCampaign'])->name('financials.campaign.delete')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/pledge', [FinancialController::class, 'storePledge'])->name('financials.pledge.store')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/pledge/{id}', [FinancialController::class, 'updatePledge'])->name('financials.pledge.update')->middleware('permission:view_offerings,manage_offerings');
        Route::post('/financials/pledge/{id}/delete', [FinancialController::class, 'deletePledge'])->name('financials.pledge.delete')->middleware('permission:view_offerings,manage_offerings');
        Route::get('/financials/members', [FinancialController::class, 'getMembers'])->name('financials.members')->middleware('permission:view_offerings,manage_offerings');

        // Reports & Analytics
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports')->middleware('permission:view_dashboard');
        Route::get('/reports/export', [ReportsController::class, 'exportCsv'])->name('reports.export')->middleware('permission:view_dashboard');

        Route::match(['get', 'post'], '/staff', [ChurchController::class, 'staff'])->name('staff')->middleware('permission:view_staff,manage_staff');

        // Newsletter
        Route::match(['get', 'post'], '/newsletters', [NewsletterController::class, 'index'])->name('newsletters')->middleware('permission:view_newsletters,send_newsletters');

        // Settings
        Route::match(['get', 'post'], '/users', [SettingsController::class, 'users'])->name('users')->middleware('permission:view_users,manage_roles');
        Route::match(['get', 'post'], '/roles', [SettingsController::class, 'roles'])->name('roles')->middleware('permission:manage_roles');
        Route::match(['get', 'post'], '/menus', [SettingsController::class, 'menus'])->name('menus')->middleware('permission:view_menus,manage_menus');
        Route::match(['get', 'post'], '/site-settings', [SettingsController::class, 'siteSettings'])->name('site-settings')->middleware('permission:view_settings,manage_settings');
        Route::match(['get', 'post'], '/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs')->middleware('permission:view_activity_logs');
    });
});
