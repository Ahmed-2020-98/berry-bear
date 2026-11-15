<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Site\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    });

    Route::middleware(['auth', 'active'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        // Content (editors + admins)
        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::resource('collections', Admin\CollectionController::class)->except('show');
        Route::resource('posts', Admin\PostController::class)->except('show');
        Route::resource('pages', Admin\PageController::class)->except('show');
        Route::resource('faqs', Admin\FaqController::class)->except('show');
        Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
        Route::post('sort/{type}', Admin\SortController::class)->name('sort');
        Route::post('uploads', Admin\UploadController::class)->name('uploads');

        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('media', [Admin\MediaController::class, 'store'])->middleware('throttle:60,1')->name('media.store');
        Route::get('media/{media}', [Admin\MediaController::class, 'show'])->name('media.show');
        Route::patch('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('content/{key}', [Admin\ContentController::class, 'edit'])->name('content.edit');
        Route::put('content/{key}', [Admin\ContentController::class, 'update'])->name('content.update');

        Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}/toggle', [Admin\MessageController::class, 'toggle'])->name('messages.toggle');
        Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        // Admin-only
        Route::middleware('can:admin')->group(function () {
            Route::get('settings', [Admin\ContentController::class, 'editSettings'])->name('settings');
            Route::put('settings', [Admin\ContentController::class, 'updateSettings'])->name('settings.update');

            Route::get('menu', [Admin\MenuController::class, 'index'])->name('menu.index');
            Route::post('menu', [Admin\MenuController::class, 'store'])->name('menu.store');
            Route::put('menu/{item}', [Admin\MenuController::class, 'update'])->name('menu.update');
            Route::delete('menu/{item}', [Admin\MenuController::class, 'destroy'])->name('menu.destroy');
            Route::post('menu-order', [Admin\MenuController::class, 'reorder'])->name('menu.reorder');

            Route::resource('users', Admin\UserController::class)->except('show');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Public site (WordPress-compatible URLs)
|--------------------------------------------------------------------------
*/
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('about', [SiteController::class, 'about'])->name('about');
Route::get('products', [SiteController::class, 'products'])->name('products');
Route::get('product/{slug}', [SiteController::class, 'product'])->name('products.show');
Route::get('collection/{slug}', [SiteController::class, 'collection'])->name('collections.show');
Route::get('faqs', [SiteController::class, 'faqs'])->name('faqs');
Route::get('contact', [SiteController::class, 'contact'])->name('contact');
Route::post('contact', [SiteController::class, 'sendContact'])->name('contact.send');
Route::get('blog', [SiteController::class, 'blog'])->name('blog');
Route::get('category/{category}', [SiteController::class, 'category'])->name('blog.category');
Route::get('search', [SiteController::class, 'search'])->name('search');
Route::redirect('wp-admin', '/admin', 301);
Route::redirect('wp-login.php', '/admin/login', 301);
Route::get('{slug}', [SiteController::class, 'slug'])->where('slug', '[^/]+')->name('posts.show');
