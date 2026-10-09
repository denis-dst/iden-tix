<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Models\Event;
use App\Models\Setting;
use App\Http\Controllers\PublicEventController;

Route::get('/', function () {
    $activeTheme = \App\Models\Setting::get('active_theme', 'default');

    $events = Event::with(['ticketCategories', 'tenant'])
        ->where('status', 'published')
        ->orderBy('event_start_date', 'asc')
        ->get();
    
    $settings = \Illuminate\Support\Facades\Cache::remember('public_settings_map', 3600, function () {
        return \App\Models\Setting::pluck('value', 'key')->toArray();
    });

    if ($activeTheme === 'new-thema') {
        return view('themes.new-thema.welcome', compact('events', 'settings', 'activeTheme'));
    }

    return view('welcome', compact('events', 'settings', 'activeTheme'));
});

Route::get('/event/{slug}', [PublicEventController::class, 'show'])->name('events.show');
Route::get('/promo/validate', [PublicEventController::class, 'validatePromo'])->name('promo.validate');
Route::post('/event/{slug}/checkout', [PublicEventController::class, 'checkout'])->name('checkout.process');
Route::match(['get', 'post'], '/wago/notification', [PublicEventController::class, 'handleWagoNotification'])->name('wago.notification');
Route::match(['get', 'post'], '/api/wago-webhook', [PublicEventController::class, 'handleWagoNotification'])->name('wago.webhook.api');
Route::match(['get', 'post'], '/wago/webhook', [PublicEventController::class, 'handleWagoNotification'])->name('wago.webhook');
Route::post('/ipaymu/notification', [PublicEventController::class, 'handleIPaymuNotification'])->name('ipaymu.notification');
Route::get('/checkout/success/{reference}', [App\Http\Controllers\PublicEventController::class, 'success'])->name('checkout.success');
Route::get('/evoucher/{reference}', [App\Http\Controllers\PublicEventController::class, 'evoucher'])->name('evoucher.public');

Route::get('/tickets/view/{code}', [App\Http\Controllers\TicketViewController::class, 'show'])->name('tickets.view');

Route::get('/lang/{locale}', [App\Http\Controllers\LanguageController::class, 'switchLang'])->name('lang.switch');

Route::get('/faq', [App\Http\Controllers\PageController::class, 'faq'])->name('faq');
Route::get('/syarat-ketentuan', [App\Http\Controllers\PageController::class, 'terms'])->name('terms');
Route::get('/terms', function() { return redirect()->route('terms'); });
Route::get('/terms-of-service', function() { return redirect()->route('terms'); });

Route::get('/refund-policy', [App\Http\Controllers\PageController::class, 'refund'])->name('refund');

Route::get('/kontak', [App\Http\Controllers\PageController::class, 'contact'])->name('contact');
Route::get('/contact', function() { return redirect()->route('contact'); });
Route::get('/contact-us', function() { return redirect()->route('contact'); });

Route::get('/flow', [App\Http\Controllers\PageController::class, 'flow'])->name('flow');
Route::get('/flow-bisnis', function() { return redirect()->route('flow'); });
Route::get('/business-flow', function() { return redirect()->route('flow'); });

Route::get('/p/{slug}', [App\Http\Controllers\PageController::class, 'show'])->name('pages.show');

Route::get('/portofolio', function () {
    return view('portofolio');
})->name('portofolio');

Route::get('/portfolio', function () {
    return redirect()->route('portofolio');
});

// Ranger Bhayangkara FC Public Registration
Route::get('/ranger-bhayangkara', [App\Http\Controllers\RangerRegistrationController::class, 'index'])->name('ranger.register');
Route::post('/ranger-bhayangkara', [App\Http\Controllers\RangerRegistrationController::class, 'store'])->name('ranger.store');

// Football Club Fan Membership Public Registration
Route::get('/join/{slug}', [App\Http\Controllers\Fan\FanRegistrationController::class, 'show'])->name('fan.register.show');
Route::post('/join/{slug}', [App\Http\Controllers\Fan\FanRegistrationController::class, 'store'])->name('fan.register.store');

// Tenant Specific Public Event Listing (e.g. /lampung-youth/list-event/)
Route::get('/{slug}/list-event', [App\Http\Controllers\TenantPublicController::class, 'listEvents'])
    ->where('slug', '^(?!organizer|superadmin|fan|api|dashboard|profile|login|register).*$')
    ->name('tenant.events');
Route::get('/{slug}/list-events', [App\Http\Controllers\TenantPublicController::class, 'listEvents'])
    ->where('slug', '^(?!organizer|superadmin|fan|api|dashboard|profile|login|register).*$')
    ->name('tenant.events.plural');



Route::middleware(['auth', 'role:Superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    
    // User & Staff Management (Directory, Impersonation & Tenant Assignment)
    Route::get('users', [App\Http\Controllers\SuperAdmin\UserController::class, 'index'])->name('users.index');
    Route::post('users', [App\Http\Controllers\SuperAdmin\UserController::class, 'store'])->name('users.store');
    Route::patch('users/{user}/assign-tenant', [App\Http\Controllers\SuperAdmin\UserController::class, 'assignTenant'])->name('users.assign-tenant');
    Route::post('users/batch-assign-tenant', [App\Http\Controllers\SuperAdmin\UserController::class, 'batchAssignTenant'])->name('users.batch-assign-tenant');

    // Tenants Trash & Resource
    Route::get('tenants/trash', [App\Http\Controllers\SuperAdmin\TenantController::class, 'trash'])->name('tenants.trash');
    Route::post('tenants/{id}/restore', [App\Http\Controllers\SuperAdmin\TenantController::class, 'restore'])->name('tenants.restore');
    Route::delete('tenants/{id}/force-delete', [App\Http\Controllers\SuperAdmin\TenantController::class, 'forceDelete'])->name('tenants.force-delete');
    Route::resource('tenants', App\Http\Controllers\SuperAdmin\TenantController::class);
    
    // Events Trash & Resource
    Route::get('events/trash', [App\Http\Controllers\SuperAdmin\EventController::class, 'trash'])->name('events.trash');
    Route::post('events/{id}/restore', [App\Http\Controllers\SuperAdmin\EventController::class, 'restore'])->name('events.restore');
    Route::delete('events/{id}/force-delete', [App\Http\Controllers\SuperAdmin\EventController::class, 'forceDelete'])->name('events.force-delete');
    Route::post('events/{event}/duplicate', [App\Http\Controllers\SuperAdmin\EventController::class, 'duplicate'])->name('events.duplicate');
    Route::resource('events', App\Http\Controllers\SuperAdmin\EventController::class);
    
    Route::resource('transactions', App\Http\Controllers\SuperAdmin\TransactionController::class);
    Route::post('transactions/{transaction}/mark-as-paid', [App\Http\Controllers\SuperAdmin\TransactionController::class, 'markAsPaid'])->name('transactions.mark-as-paid');
    Route::post('transactions/{transaction}/resend-evoucher', [App\Http\Controllers\SuperAdmin\TransactionController::class, 'resendEvoucher'])->name('transactions.resend-evoucher');
    Route::get('transactions/{transaction}/print-evoucher', [App\Http\Controllers\SuperAdmin\TransactionController::class, 'printEvoucher'])->name('transactions.print-evoucher');
    Route::post('tickets/{ticket}/cancel', [App\Http\Controllers\SuperAdmin\TransactionController::class, 'cancelTicket'])->name('tickets.cancel');
    Route::post('transactions/{transaction}/cancel', [App\Http\Controllers\SuperAdmin\TransactionController::class, 'cancelTransaction'])->name('transactions.cancel');
    Route::post('transactions/{transaction}/cancel-tickets', [App\Http\Controllers\SuperAdmin\TransactionController::class, 'cancelTickets'])->name('transactions.cancel-tickets');
    Route::get('reports', [App\Http\Controllers\SuperAdmin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [App\Http\Controllers\SuperAdmin\ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('settings', [App\Http\Controllers\SuperAdmin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [App\Http\Controllers\SuperAdmin\SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/theme-switch', [App\Http\Controllers\SuperAdmin\SettingController::class, 'switchTheme'])->name('settings.theme-switch');

    // Mail Monitor (Incoming IMAP/POP3 & Outgoing SMTP)
    Route::get('mail', [App\Http\Controllers\SuperAdmin\MailMonitorController::class, 'index'])->name('mail.index');
    Route::post('mail/inbox', [App\Http\Controllers\SuperAdmin\MailMonitorController::class, 'fetchInbox'])->name('mail.inbox');
    Route::post('mail/message/{id}', [App\Http\Controllers\SuperAdmin\MailMonitorController::class, 'readMessage'])->name('mail.message');
    Route::post('mail/test-smtp', [App\Http\Controllers\SuperAdmin\MailMonitorController::class, 'testSmtp'])->name('mail.test-smtp');
    Route::post('mail/test-incoming', [App\Http\Controllers\SuperAdmin\MailMonitorController::class, 'testIncoming'])->name('mail.test-incoming');

    // Ranger Management
    Route::get('rangers', [App\Http\Controllers\SuperAdmin\RangerController::class, 'index'])->name('rangers.index');
    Route::post('rangers/quotas', [App\Http\Controllers\SuperAdmin\RangerController::class, 'updateQuotas'])->name('rangers.update-quotas');
    Route::post('rangers/generate', [App\Http\Controllers\SuperAdmin\RangerController::class, 'generateCrew'])->name('rangers.generate');
    Route::post('rangers/reset', [App\Http\Controllers\SuperAdmin\RangerController::class, 'resetAssignments'])->name('rangers.reset');
    Route::patch('rangers/{ranger}/assignment', [App\Http\Controllers\SuperAdmin\RangerController::class, 'updateAssignment'])->name('rangers.update-assignment');
    Route::post('rangers/{ranger}/toggle-offday', [App\Http\Controllers\SuperAdmin\RangerController::class, 'toggleOffday'])->name('rangers.toggle-offday');
    Route::post('rangers/{ranger}/toggle-spv', [App\Http\Controllers\SuperAdmin\RangerController::class, 'toggleSpv'])->name('rangers.toggle-spv');
    Route::delete('rangers/{ranger}', [App\Http\Controllers\SuperAdmin\RangerController::class, 'destroy'])->name('rangers.destroy');
    Route::get('rangers/export', [App\Http\Controllers\SuperAdmin\RangerController::class, 'export'])->name('rangers.export');

    // Invoice Management
    Route::post('invoices/{invoice}/send', [App\Http\Controllers\SuperAdmin\InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/confirm-payment', [App\Http\Controllers\SuperAdmin\InvoiceController::class, 'confirmPayment'])->name('invoices.confirm-payment');
    Route::get('invoices/{invoice}/download-pdf', [App\Http\Controllers\SuperAdmin\InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');
    Route::get('invoices/{invoice}/view-proof', [App\Http\Controllers\SuperAdmin\InvoiceController::class, 'viewProof'])->name('invoices.view-proof');
    Route::resource('invoices', App\Http\Controllers\SuperAdmin\InvoiceController::class);
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->hasRole('Superadmin')) {
        return redirect()->route('superadmin.dashboard');
    }
    
    if ($user->hasRole('Penyedia Event')) {
        return redirect()->route('organizer.dashboard');
    }

    if ($user->hasRole('Petugas Loket')) {
        return redirect()->route('organizer.redeem.index');
    }

    if ($user->hasRole('Petugas Gate')) {
        return redirect()->route('organizer.gate.index');
    }
    // Fallback for other roles or unassigned
    return redirect('/');
})->middleware(['auth'])->name('dashboard');

// Impersonation Routes
Route::middleware('auth')->group(function () {
    Route::match(['GET', 'POST'], '/impersonate/leave', [App\Http\Controllers\ImpersonateController::class, 'leave'])->name('impersonate.leave');
    Route::post('/impersonate/tenant/{tenant}/{role}', [App\Http\Controllers\ImpersonateController::class, 'impersonateTenantRole'])->name('impersonate.tenant.role');
    Route::post('/impersonate/event/{event}/{role}', [App\Http\Controllers\ImpersonateController::class, 'impersonateEventRole'])->name('impersonate.event.role');
    Route::post('/impersonate/{user}', [App\Http\Controllers\ImpersonateController::class, 'impersonate'])->name('impersonate.start')->whereNumber('user');
});

Route::middleware(['auth', 'role:Superadmin|Penyedia Event|Petugas Loket|Petugas Gate', 'tenant.status'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Organizer\DashboardController::class, 'index'])->name('dashboard');
    
    // Event Management
    Route::post('events/bulk-duplicate', [App\Http\Controllers\Organizer\EventController::class, 'bulkDuplicate'])->name('events.bulk-duplicate');
    Route::post('events/{event}/duplicate', [App\Http\Controllers\Organizer\EventController::class, 'duplicate'])->name('events.duplicate');
    Route::resource('events', App\Http\Controllers\Organizer\EventController::class);
    Route::resource('events.categories', App\Http\Controllers\Organizer\TicketCategoryController::class);
    Route::get('categories/{category}/print-wristbands', [App\Http\Controllers\WristbandPrintController::class, 'print'])->name('categories.print-wristbands');
    Route::post('categories/{category}/reset-offline-stock', [App\Http\Controllers\WristbandPrintController::class, 'resetOfflineStock'])->name('categories.reset-offline-stock');
    
    // Voucher/Promo Management
    Route::resource('vouchers', App\Http\Controllers\Organizer\PromoCodeController::class);
    
    // Reports & Operations
    Route::get('reports', [App\Http\Controllers\Organizer\ReportController::class, 'index'])->middleware('role:Penyedia Event')->name('reports.index');
    Route::get('reports/duplicates', [App\Http\Controllers\Organizer\ReportController::class, 'duplicates'])->middleware('role:Penyedia Event')->name('reports.duplicates');
    Route::get('reports/export-excel', [App\Http\Controllers\Organizer\ReportController::class, 'exportExcel'])->middleware('role:Penyedia Event')->name('reports.export-excel');
    Route::get('checkin', [App\Http\Controllers\Organizer\CheckinController::class, 'index'])->name('checkin.index');
    Route::post('checkin/{id}/redeem', [App\Http\Controllers\Organizer\CheckinController::class, 'redeem'])->name('checkin.redeem');
    
    // Finance
    Route::get('finance', [App\Http\Controllers\Organizer\FinanceController::class, 'index'])->name('finance.index');
    
    // Crew Management
    Route::resource('crews', App\Http\Controllers\Organizer\CrewController::class);

    // Sales Transactions & E-Voucher
    Route::middleware('role:Penyedia Event|Petugas Loket')->group(function () {
        Route::get('pos', [App\Http\Controllers\Organizer\POSController::class, 'index'])->name('pos.index');
        Route::get('pos/events/{event}', [App\Http\Controllers\Organizer\POSController::class, 'create'])->name('pos.create');
        Route::post('pos/events/{event}', [App\Http\Controllers\Organizer\POSController::class, 'store'])->name('pos.store');
        Route::get('pos/transactions/{transaction}/print', [App\Http\Controllers\Organizer\POSController::class, 'print'])->name('pos.print');
    });
    Route::get('transactions', [App\Http\Controllers\Organizer\TransactionController::class, 'index'])->name('transactions.index');
    Route::post('transactions/{transaction}/mark-as-paid', [App\Http\Controllers\Organizer\TransactionController::class, 'markAsPaid'])->name('transactions.mark-as-paid');
    Route::post('transactions/{transaction}/resend-evoucher', [App\Http\Controllers\Organizer\TransactionController::class, 'resendEvoucher'])->name('transactions.resend-evoucher');
    Route::get('transactions/{transaction}/print-evoucher', [App\Http\Controllers\Organizer\TransactionController::class, 'printEvoucher'])->name('transactions.print-evoucher');
    Route::post('transactions/{transaction}/send-whatsapp', [App\Http\Controllers\Organizer\TransactionController::class, 'sendWhatsApp'])->name('transactions.send-whatsapp');
    Route::post('tickets/{ticket}/cancel', [App\Http\Controllers\Organizer\TransactionController::class, 'cancelTicket'])->name('tickets.cancel');
    Route::post('transactions/{transaction}/cancel', [App\Http\Controllers\Organizer\TransactionController::class, 'cancelTransaction'])->name('transactions.cancel');
    Route::post('transactions/{transaction}/cancel-tickets', [App\Http\Controllers\Organizer\TransactionController::class, 'cancelTickets'])->name('transactions.cancel-tickets');

    // Redeem System
    Route::get('redeem', [App\Http\Controllers\Organizer\RedeemController::class, 'index'])->name('redeem.index');
    Route::get('redeem/{event}/verify', [App\Http\Controllers\Organizer\RedeemController::class, 'verifyForm'])->name('redeem.verify');
    Route::post('redeem/{event}/verify', [App\Http\Controllers\Organizer\RedeemController::class, 'verify'])->name('redeem.verify.post');
    Route::get('redeem/{event}/scan', [App\Http\Controllers\Organizer\RedeemController::class, 'scan'])->name('redeem.scan');
    Route::post('redeem/check', [App\Http\Controllers\Organizer\RedeemController::class, 'check'])->name('redeem.check');
    Route::get('redeem/{event}/download', [App\Http\Controllers\Organizer\RedeemController::class, 'downloadData'])->name('redeem.download');
    Route::post('redeem/process', [App\Http\Controllers\Organizer\RedeemController::class, 'process'])->name('redeem.process');

    // Gate System (Automatic Scan)
    Route::resource('events.gates', App\Http\Controllers\Organizer\GateManagementController::class);
    Route::get('gate', [App\Http\Controllers\Organizer\GateController::class, 'index'])->name('gate.index');
    Route::get('gate/{event}/verify', [App\Http\Controllers\Organizer\GateController::class, 'verifyForm'])->name('gate.verify');
    Route::post('gate/{event}/verify', [App\Http\Controllers\Organizer\GateController::class, 'verify'])->name('gate.verify.post');
    Route::get('gate/{event}/setup', [App\Http\Controllers\Organizer\GateController::class, 'setupForm'])->name('gate.setup');
    Route::post('gate/{event}/setup', [App\Http\Controllers\Organizer\GateController::class, 'setup'])->name('gate.setup.post');
    Route::get('gate/{event}/scan', [App\Http\Controllers\Organizer\GateController::class, 'scan'])->name('gate.scan');
    Route::post('gate/process', [App\Http\Controllers\Organizer\GateController::class, 'process'])->name('gate.process');
    Route::post('gate/{event}/bulk-checkin', [App\Http\Controllers\Organizer\GateController::class, 'bulkCheckin'])->name('gate.bulk-checkin');

    // Tenant Settings (T&C)
    Route::get('settings/terms', [App\Http\Controllers\Organizer\TenantSettingsController::class, 'editTerms'])->name('settings.terms');
    Route::post('settings/terms', [App\Http\Controllers\Organizer\TenantSettingsController::class, 'updateTerms'])->name('settings.terms.update');

    // Invoice (Tenant View)
    Route::get('invoices', [App\Http\Controllers\Organizer\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [App\Http\Controllers\Organizer\InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('invoices/{invoice}/upload-proof', [App\Http\Controllers\Organizer\InvoiceController::class, 'uploadProof'])->name('invoices.upload-proof');
    Route::get('invoices/{invoice}/download-pdf', [App\Http\Controllers\Organizer\InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');
    Route::post('invoices/dismiss-modal', [App\Http\Controllers\Organizer\InvoiceController::class, 'dismissModal'])->name('invoices.dismiss-modal');

    // Football Club Membership & Fan CRM
    Route::prefix('membership')->name('membership.')->group(function () {
        Route::get('tiers', [App\Http\Controllers\Organizer\MembershipController::class, 'tiersIndex'])->name('tiers.index');
        Route::get('tiers/create', [App\Http\Controllers\Organizer\MembershipController::class, 'tiersCreate'])->name('tiers.create');
        Route::post('tiers', [App\Http\Controllers\Organizer\MembershipController::class, 'tiersStore'])->name('tiers.store');
        Route::get('tiers/{tier}/edit', [App\Http\Controllers\Organizer\MembershipController::class, 'tiersEdit'])->name('tiers.edit');
        Route::put('tiers/{tier}', [App\Http\Controllers\Organizer\MembershipController::class, 'tiersUpdate'])->name('tiers.update');
        Route::delete('tiers/{tier}', [App\Http\Controllers\Organizer\MembershipController::class, 'tiersDestroy'])->name('tiers.destroy');

        Route::get('members', [App\Http\Controllers\Organizer\MembershipController::class, 'membersIndex'])->name('members.index');
        Route::get('members/{member}', [App\Http\Controllers\Organizer\MembershipController::class, 'membersShow'])->name('members.show');
        Route::post('members/{member}/verify-kyc', [App\Http\Controllers\Organizer\MembershipController::class, 'verifyKyc'])->name('members.verify-kyc');
    });

    // Season Pass (Tiket Terusan)
    Route::prefix('season-passes')->name('season-passes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Organizer\SeasonPassController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Organizer\SeasonPassController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Organizer\SeasonPassController::class, 'store'])->name('store');
    });

    // Gamifikasi & Tebak Skor
    Route::prefix('gamification')->name('gamification.')->group(function () {
        Route::get('/', [App\Http\Controllers\Organizer\GamificationController::class, 'index'])->name('index');
        Route::post('/matches/{event}/score', [App\Http\Controllers\Organizer\GamificationController::class, 'updateMatchScore'])->name('update-score');
        Route::post('/quizzes', [App\Http\Controllers\Organizer\GamificationController::class, 'storeQuiz'])->name('store-quiz');
    });

    // Korwil Suporter
    Route::prefix('korwil')->name('korwil.')->group(function () {
        Route::get('/', [App\Http\Controllers\Organizer\KorwilManagementController::class, 'index'])->name('index');
        Route::post('/store-korwil', [App\Http\Controllers\Organizer\KorwilManagementController::class, 'storeKorwil'])->name('store-korwil');
        Route::post('/store-allocation', [App\Http\Controllers\Organizer\KorwilManagementController::class, 'storeAllocation'])->name('store-allocation');
    });
});

// Fan Portal (Suporter Club Zone & KYC)
Route::middleware('auth')->prefix('fan')->name('fan.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Fan\FanPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/kyc', [App\Http\Controllers\Fan\FanPortalController::class, 'showKyc'])->name('kyc');
    Route::post('/kyc', [App\Http\Controllers\Fan\FanPortalController::class, 'submitKyc'])->name('kyc.submit');
    Route::get('/season-pass', [App\Http\Controllers\Fan\FanPortalController::class, 'seasonPass'])->name('season-pass');
    Route::post('/season-pass/{event}/{seasonPass}/claim', [App\Http\Controllers\Fan\FanPortalController::class, 'claimTicket'])->name('season-pass.claim');
    Route::get('/game-zone', [App\Http\Controllers\Fan\FanPortalController::class, 'gameZone'])->name('game-zone');
    Route::post('/game-zone/predict/{event}', [App\Http\Controllers\Fan\FanPortalController::class, 'submitPrediction'])->name('game-zone.predict');
    Route::post('/game-zone/quiz/{quiz}', [App\Http\Controllers\Fan\FanPortalController::class, 'submitQuiz'])->name('game-zone.quiz');
    Route::post('/korwil-consent/{consent}/respond', [App\Http\Controllers\Fan\FanPortalController::class, 'respondKorwilConsent'])->name('korwil-consent.respond');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
