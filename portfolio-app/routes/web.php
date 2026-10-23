<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Site;
use App\Support\RoundedFavicon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::middleware(['locale:site', 'track'])->group(function () {
    Route::get('/', [Site\PageController::class, 'home'])->name('home');
    Route::get('/about', [Site\PageController::class, 'about'])->name('about');
    Route::get('/projects', [Site\PageController::class, 'projects'])->name('projects.index');
    Route::get('/projects/{project}', [Site\PageController::class, 'project'])->name('projects.show');
    Route::get('/contact', [Site\PageController::class, 'contact'])->name('contact');
});

Route::middleware('locale:site')->group(function () {
    Route::post('/contact', [Site\ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
    Route::get('/lang/{locale}', [Site\PageController::class, 'language'])->name('language');
    Route::get('/sitemap.xml', [Site\PageController::class, 'sitemap'])->name('sitemap');
    Route::get('/manifest.webmanifest', [Site\AppShellController::class, 'manifest'])->name('manifest');
    Route::get('/offline', [Site\AppShellController::class, 'offline'])->name('offline');
    Route::get('/favicon-rounded.png', function () {
        $png = RoundedFavicon::png();
        abort_unless($png, 404);

        return response($png, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=31536000, immutable']);
    })->name('favicon.rounded');
    Route::get('/cv/{locale?}', Site\CvDownloadController::class)->middleware('throttle:30,1')->name('cv.download');

    // Visitor accounts (guard "member" — separate from admin users)
    Route::middleware('guest:member,web')->group(function () {
        Route::get('/login', [Site\Auth\MemberLoginController::class, 'create'])->name('member.login');
        Route::post('/login', [Site\Auth\MemberLoginController::class, 'store'])->middleware('throttle:login');
        Route::get('/register', [Site\Auth\MemberRegisterController::class, 'create'])->name('member.register');
        Route::post('/register', [Site\Auth\MemberRegisterController::class, 'store'])->middleware('throttle:login');
        // Continue with Google / Apple (Apple posts its callback, so both methods are allowed there)
        Route::get('/auth/{provider}/redirect', [Site\Auth\SocialLoginController::class, 'redirect'])->whereIn('provider', Site\Auth\SocialLoginController::PROVIDERS)->middleware('throttle:login')->name('member.social.redirect');
        Route::match(['get', 'post'], '/auth/{provider}/callback', [Site\Auth\SocialLoginController::class, 'callback'])->whereIn('provider', Site\Auth\SocialLoginController::PROVIDERS)->middleware('throttle:login')->name('member.social.callback');
    });
    Route::post('/logout', [Site\Auth\MemberLoginController::class, 'destroy'])->middleware('auth:member')->name('member.logout');
    Route::middleware('auth:member')->group(function () {
        Route::get('/account', [Site\Auth\MemberAccountController::class, 'show'])->name('member.account');
        Route::put('/account', [Site\Auth\MemberAccountController::class, 'update'])->name('member.account.update');
        Route::put('/account/password', [Site\Auth\MemberAccountController::class, 'password'])->middleware('throttle:login')->name('member.account.password');
    });
});

// URLs of the original static site
Route::permanentRedirect('/index.html', '/');
Route::permanentRedirect('/about.html', '/about');
Route::permanentRedirect('/projects.html', '/projects');
Route::permanentRedirect('/contact.html', '/contact');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('locale:admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\Auth\LoginController::class, 'create'])->name('login');
        Route::post('login', [Admin\Auth\LoginController::class, 'store'])->middleware('throttle:login');
        Route::get('forgot-password', [Admin\Auth\PasswordResetController::class, 'requestForm'])->name('password.request');
        Route::post('forgot-password', [Admin\Auth\PasswordResetController::class, 'sendLink'])->middleware('throttle:login')->name('password.email');
        Route::get('reset-password/{token}', [Admin\Auth\PasswordResetController::class, 'resetForm'])->name('password.reset');
        Route::post('reset-password', [Admin\Auth\PasswordResetController::class, 'reset'])->middleware('throttle:login')->name('password.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [Admin\Auth\LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('analytics', [Admin\DashboardController::class, 'analytics'])->name('analytics');
        Route::post('locale/{locale}', [Admin\LocaleController::class, 'update'])->name('locale');
        Route::get('icons', [Admin\LocaleController::class, 'icons'])->name('icons');
        Route::get('icons/{name}.svg', [Admin\LocaleController::class, 'icon'])->name('icon');

        // Content — Super Admin and Editor
        Route::post('projects/{project}/duplicate', [Admin\ProjectController::class, 'duplicate'])->name('projects.duplicate');
        Route::resource('projects', Admin\ProjectController::class);
        Route::resource('services', Admin\ServiceController::class)->except('show');
        Route::resource('skills', Admin\SkillController::class)->except('show');
        Route::resource('experience', Admin\ExperienceController::class)->except('show');
        Route::resource('education', Admin\EducationController::class)->except('show');
        Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
        Route::resource('certificates', Admin\CertificateController::class)->except('show');
        Route::post('reorder/{type}', Admin\ReorderController::class)->name('reorder');

        Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::patch('messages/{message}', [Admin\MessageController::class, 'update'])->name('messages.update');
        Route::post('messages/{message}/reply', [Admin\MessageController::class, 'reply'])->middleware('throttle:20,1')->name('messages.reply');
        Route::delete('messages/trash', [Admin\MessageController::class, 'emptyTrash'])->name('messages.empty-trash');
        Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        Route::resource('media', Admin\MediaController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::get('notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('notifications/clear', [Admin\NotificationController::class, 'clear'])->name('notifications.clear');
        Route::post('notifications/{notification}/read', [Admin\NotificationController::class, 'read'])->name('notifications.read');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [Admin\ProfileController::class, 'password'])->name('profile.password');

        // Website, settings, users, security log — Super Admin only
        Route::middleware('can:super-admin')->group(function () {
            Route::get('website', [Admin\WebsiteController::class, 'index'])->name('website.index');
            Route::get('website/hero', [Admin\WebsiteController::class, 'hero'])->name('website.hero');
            Route::put('website/hero', [Admin\WebsiteController::class, 'updateHero']);
            Route::get('website/about', [Admin\WebsiteController::class, 'about'])->name('website.about');
            Route::put('website/about', [Admin\WebsiteController::class, 'updateAbout']);
            Route::get('website/contact', [Admin\WebsiteController::class, 'contact'])->name('website.contact');
            Route::put('website/contact', [Admin\WebsiteController::class, 'updateContact']);
            Route::get('cv', [Admin\CvController::class, 'edit'])->name('cv.edit');
            Route::post('cv', [Admin\CvController::class, 'update'])->name('cv.update');
            Route::get('cv/{locale}/download', [Admin\CvController::class, 'download'])->name('cv.download');
            Route::delete('cv/{locale}', [Admin\CvController::class, 'destroy'])->name('cv.destroy');

            Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/mail-test', [Admin\SettingsController::class, 'testMail'])->middleware('throttle:10,1')->name('settings.mail-test');
            Route::resource('users', Admin\UserController::class)->except('show');
            Route::resource('members', Admin\MemberController::class)->only(['index', 'destroy']);
            Route::get('activity', [Admin\ActivityController::class, 'index'])->name('activity.index');
        });
    });
});

Route::get('/robots.txt', fn () => response("User-agent: *\nDisallow: /admin\n\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']));
