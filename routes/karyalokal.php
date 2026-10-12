<?php

use App\Http\Controllers\ArtisanProductController;
use App\Http\Controllers\ArtisanCustomerChatController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CraftStoryController;
use App\Http\Controllers\CuratorController;
use App\Http\Controllers\MarketplaceAdminController;
use App\Http\Controllers\AdminProductSawController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentVerificationController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/tentang-kami', [StorefrontController::class, 'about'])->name('about');
Route::get('/artikel', [ArticleController::class, 'publicIndex'])->name('articles.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'publicShow'])->where('slug', '[a-z0-9-]+')->name('articles.show');
Route::get('/karya', [StorefrontController::class, 'index'])->name('storefront.catalog');
Route::get('/karya/{slug}', [StorefrontController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')->name('products.show');
Route::get('/craft/{craftId}', [CraftStoryController::class, 'show'])
    ->where('craftId', 'KL-[0-9]{4}-[0-9]{6}')->name('craft.story');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::put('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:customer')->group(function () {
        Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');
        Route::get('/chat', [ArtisanCustomerChatController::class, 'customerIndex'])->name('chat.customer.index');
        Route::post('/chat', [ArtisanCustomerChatController::class, 'startCustomerChat'])->name('chat.customer.start');
        Route::get('/chat/{chat}', [ArtisanCustomerChatController::class, 'customerShow'])->name('chat.customer.show');
        Route::post('/chat/{chat}/messages', [ArtisanCustomerChatController::class, 'customerMessage'])->name('chat.customer.message');
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
        Route::post('/orders/{order}/items/{orderItem}/reviews', [ProductReviewController::class, 'storeForOrderItem'])->name('orders.items.reviews.store');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::post('/orders/{order}/payment-proof', [PaymentVerificationController::class, 'submitProof'])->name('orders.payment-proof');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
        Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

        Route::prefix('bantuan/tickets')->name('support.customer.')->group(function () {
            Route::get('/', [SupportTicketController::class, 'customerIndex'])->name('index');
            Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
            Route::post('/', [SupportTicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
        });
    });

    Route::middleware('role:artisan')->prefix('artisan')->name('artisan.')->group(function () {
        Route::get('/dashboard', [ArtisanProductController::class, 'dashboard'])->name('dashboard');
        Route::get('/sales', [ArtisanProductController::class, 'salesReport'])->name('sales.index');
        Route::get('/chats', [ArtisanCustomerChatController::class, 'artisanIndex'])->name('chats.index');
        Route::get('/chats/{chat}', [ArtisanCustomerChatController::class, 'artisanShow'])->name('chats.show');
        Route::post('/chats/{chat}/messages', [ArtisanCustomerChatController::class, 'artisanMessage'])->name('chats.message');
        Route::post('/products/{product}/reviews/{review}/reply', [ArtisanProductController::class, 'replyReview'])->name('products.reviews.reply');
        Route::resource('products', ArtisanProductController::class)->except(['destroy']);
    });

    Route::middleware('role:curator')->prefix('curator')->name('curator.')->group(function () {
        Route::get('/dashboard', [CuratorController::class, 'dashboard'])->name('dashboard');
        Route::get('/products', [CuratorController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [CuratorController::class, 'show'])->name('products.show');
        Route::post('/products/{product}/decision', [CuratorController::class, 'decide'])->name('products.decide');
    });

    Route::middleware('role:customer_service')->prefix('support')->name('support.')->group(function () {
        Route::get('/dashboard', [SupportTicketController::class, 'dashboard'])->name('dashboard');
        Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->whereNumber('ticket')->name('agent.show');
        Route::put('/tickets/{ticket}', [SupportTicketController::class, 'update'])->name('agent.update');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [MarketplaceAdminController::class, 'index'])->name('dashboard');
        Route::get('/spk-saw', [AdminProductSawController::class, 'index'])->name('saw.index');
        Route::get('/articles', [ArticleController::class, 'adminIndex'])->name('articles.index');
        Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
        Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
        Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
        Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
        Route::get('/sales', [MarketplaceAdminController::class, 'salesReport'])->name('sales.index');
        Route::get('/orders/{order}', [MarketplaceAdminController::class, 'orderShow'])->name('orders.show');
        Route::put('/orders/{order}/tracking', [MarketplaceAdminController::class, 'updateTracking'])->name('orders.tracking');
        Route::get('/pending-products', [MarketplaceAdminController::class, 'pendingProductsIndex'])->name('pending-products.index');
        Route::get('/pending-products/{product}', [MarketplaceAdminController::class, 'pendingProductShow'])->name('pending-products.show');
        Route::get('/products/{product}', [MarketplaceAdminController::class, 'showProduct'])->name('products.show');
        Route::get('/products/{product}/edit', [MarketplaceAdminController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{product}', [MarketplaceAdminController::class, 'updateProduct'])->name('products.update');
        Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
        Route::put('/tickets/{ticket}', [SupportTicketController::class, 'update'])->name('tickets.update');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');
        Route::get('/payments', [PaymentVerificationController::class, 'index'])->name('payments.index');
        Route::get('/payments/{order}/proof', [PaymentVerificationController::class, 'downloadProof'])->name('payments.proof');
        Route::put('/payments/{order}', [PaymentVerificationController::class, 'update'])->name('payments.update');
        Route::get('/qr', [MarketplaceAdminController::class, 'qrIndex'])->name('qr.index');
        Route::post('/qr/{product}/toggle', [MarketplaceAdminController::class, 'toggleQr'])->name('qr.toggle');
        Route::post('/qr/{product}/regenerate', [MarketplaceAdminController::class, 'regenerateQr'])->name('qr.regenerate');
    });
});