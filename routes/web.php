<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
Route::get('/cars/compare', [CarController::class, 'compare'])->name('cars.compare');
Route::get('/cars/{car}', [CarController::class, 'show'])->name('cars.show');
Route::get('/favorites', [CarController::class, 'favorites'])->name('cars.favorites');
Route::get('/store', fn () => view('store'))->name('store');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Contact Routes
Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');
Route::get('/contact/thanks', [App\Http\Controllers\ContactController::class, 'thanks'])->name('contact.thanks');

// Buy / Appraisal Routes
Route::get('/buy', [App\Http\Controllers\BuyController::class, 'index'])->name('buy.index');
Route::post('/buy', [App\Http\Controllers\BuyController::class, 'send'])->name('buy.send');
Route::get('/buy/thanks', [App\Http\Controllers\BuyController::class, 'thanks'])->name('buy.thanks');

// Auth Routes (Dashboard)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Auth Routes (Profile)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin
    Route::middleware('admin')->group(function (): void {
        Route::get('admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('admin/cars/export', [\App\Http\Controllers\Admin\CarController::class, 'export'])->name('admin.cars.export');
        Route::delete('admin/cars/{car}/images/{image}', [\App\Http\Controllers\Admin\CarController::class, 'imageDestroy'])->name('admin.cars.images.destroy');
        Route::patch('admin/cars/{car}/status', [\App\Http\Controllers\Admin\CarController::class, 'updateStatus'])->name('admin.cars.updateStatus');
        Route::patch('admin/cars/{car}/featured', [\App\Http\Controllers\Admin\CarController::class, 'toggleFeatured'])->name('admin.cars.toggleFeatured');
        Route::resource('admin/cars', \App\Http\Controllers\Admin\CarController::class)->names('admin.cars');
        Route::resource('admin/inquiries', \App\Http\Controllers\Admin\InquiryController::class)
            ->only(['index', 'show', 'destroy'])
            ->names('admin.inquiries');
        Route::resource('admin/appraisals', \App\Http\Controllers\Admin\AppraisalController::class)
            ->only(['index', 'show', 'destroy'])
            ->names('admin.appraisals');
    });
});

require __DIR__.'/auth.php';
