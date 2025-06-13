<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PanitiaController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\RegistrasiController;

// Home route
Route::get('/', [GuestController::class, 'index'])->name('home');
Route::get('/dashboard', [GuestController::class, 'index'])->name('home');

// Event detail route
Route::get('/event/{eventId}', [GuestController::class, 'showEventDetail'])->name('event.detail');

// API Routes untuk komunikasi dengan Node.js backend
Route::prefix('api')->group(function () {
    // Route untuk mendapatkan semua event
    Route::get('/events/all', [GuestController::class, 'getAllEvents'])->name('api.events.all');
    
    // Route untuk pencarian event
    Route::get('/events/search', [GuestController::class, 'searchEvents'])->name('api.events.search');
    
    // Route untuk detail event
    Route::get('/events/{eventId}', [GuestController::class, 'getEventDetail'])->name('api.events.detail');
    
    // Route untuk testing koneksi
    Route::get('/test-connection', [GuestController::class, 'testConnection'])->name('api.test.connection');
});

// Authentication routes
Route::get('/login', function () {
    return view('login');
})->name('login.form');

Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard routes
Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.dashboard');
Route::get('/panitia', [PanitiaController::class, 'index'])->name('panitia.dashboard');
Route::get('/member', [MemberController::class, 'index'])->name('member.dashboard');

// Panitia event management routes
Route::get('/panitia/add', [EventController::class, 'addEvent'])->name('panitia.addEvent');
Route::post('/panitia/submit', [EventController::class, 'submitEvent'])->name('panitia.submitEvent');

Route::get('/testing', [TestingController::class, 'index'])->name('layouts.index');

// Panitia routes group
Route::prefix('panitia')->group(function () {
    Route::delete('/event/{id}/delete', [EventController::class, 'deleteEvent'])->name('panitia.deleteEvent');
    Route::get('/events', [EventController::class, 'listEvents'])->name('panitia.listEvents');
    Route::get('/event/{id}', [EventController::class, 'detail'])->name('panitia.detailEvent');
    Route::get('/event/{id}/edit', [EventController::class, 'editEvent'])->name('panitia.editEvent');
    Route::put('/event/{id}/update', [EventController::class, 'updateEvent'])->name('panitia.updateEvent');
});

// API route for panitia event detail
Route::get('/api/panitia/events/{id}/detail', [EventController::class, 'detail']);

Route::get('/events/{id}', [DetailController::class, 'show'])->name('events.show');

Route::get('/events/{eventId}/tickets', [RegistrasiController::class, 'showTicketForm'])->name('event.purchase');

// Process ticket registration
Route::post('/events/{eventId}/tickets', [RegistrasiController::class, 'processRegistration'])->name('event.process');

// Success page
Route::get('/tickets/success', [RegistrasiController::class, 'showSuccessPage'])->name('event.success');

// User's ticket history
Route::get('/my-tickets', [RegistrasiController::class, 'showTicketHistory'])->name('event.history');

// Route::get('/dashboard')