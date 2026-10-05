<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\RandiController;
use App\Http\Controllers\RandiAdminController;
use App\Http\Middleware\RandiAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/skills', [PortfolioController::class, 'skills'])->name('skills');
Route::get('/statistics', [PortfolioController::class, 'statistics'])->name('statistics');
Route::post('/statistics/login', [PortfolioController::class, 'authenticateStatistics'])->middleware('throttle:portfolio-statistics-login')->name('statistics.login');
Route::post('/statistics/logout', [PortfolioController::class, 'logoutStatistics'])->name('statistics.logout');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:portfolio-contact')->name('contact.send');

Route::prefix('randi')->name('randi.')->group(function () {
    Route::get('/', [RandiController::class, 'show'])->name('demo');
    Route::post('/form', [RandiController::class, 'form'])->middleware('throttle:randi-response')->name('demo.form');
    Route::post('/response', [RandiController::class, 'respond'])->middleware('throttle:randi-response')->name('demo.response');
    Route::get('/admin/login', [RandiAdminController::class, 'login'])->name('admin.login');
    Route::post('/admin/login', [RandiAdminController::class, 'authenticate'])->middleware('throttle:randi-login')->name('admin.authenticate');
    Route::middleware(RandiAdmin::class)->group(function () {
        Route::get('/admin', [RandiAdminController::class, 'index'])->name('admin');
        Route::post('/admin/logout', [RandiAdminController::class, 'logout'])->name('admin.logout');
        Route::post('/admin/invitations', [RandiAdminController::class, 'create'])->middleware('throttle:randi-admin')->name('admin.create');
        Route::post('/admin/invitations/{id}/revoke', [RandiAdminController::class, 'revoke'])->whereNumber('id')->middleware('throttle:randi-admin')->name('admin.revoke');
        Route::post('/admin/invitations/{id}/delete', [RandiAdminController::class, 'delete'])->whereNumber('id')->middleware('throttle:randi-admin')->name('admin.delete');
    });
    Route::get('/{token}', [RandiController::class, 'show'])->where('token', '[A-Za-z0-9_-]{43}')->name('show');
    Route::post('/{token}/form', [RandiController::class, 'form'])->where('token', '[A-Za-z0-9_-]{43}')->middleware('throttle:randi-response')->name('form');
    Route::post('/{token}/response', [RandiController::class, 'respond'])->where('token', '[A-Za-z0-9_-]{43}')->middleware('throttle:randi-response')->name('response');
});
