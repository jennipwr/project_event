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
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\RegisterAdminController;
use App\Http\Controllers\AdminAkunController;
use App\Http\Middleware\CekRoleDanStatus;

// Home route
Route::get('/', [GuestController::class, 'index'])->name('home');
Route::get('/dashboard', [GuestController::class, 'index'])->name('home');

// Guest routes
Route::get('/event/{eventId}', [GuestController::class, 'showEventDetail'])->name('event.detail');
Route::prefix('api')->group(function () {
    Route::get('/events/all', [GuestController::class, 'getAllEvents'])->name('api.events.all');
    Route::get('/events/search', [GuestController::class, 'searchEvents'])->name('api.events.search');
    Route::get('/events/{eventId}', [GuestController::class, 'getEventDetail'])->name('api.events.detail');
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

Route::get('/testing', [TestingController::class, 'index'])->name('layouts.index');

// Panitia routes
Route::middleware([CekRoleDanStatus::class . ':2'])->group(function () {
    Route::get('/panitia', [PanitiaController::class, 'index'])->name('panitia.dashboard');
    Route::get('/panitia/add', [EventController::class, 'addEvent'])->name('panitia.addEvent');
    Route::post('/panitia/submit', [EventController::class, 'submitEvent'])->name('panitia.submitEvent');

    Route::prefix('panitia')->group(function () {
        Route::delete('/event/{id}/delete', [EventController::class, 'deleteEvent'])->name('panitia.deleteEvent');
        Route::get('/events', [EventController::class, 'listEvents'])->name('panitia.listEvents');
        Route::get('/event/{id}', [EventController::class, 'detail'])->name('panitia.detailEvent');
        Route::get('/event/{id}/edit', [EventController::class, 'editEvent'])->name('panitia.editEvent');
        Route::put('/event/{id}/update', [EventController::class, 'updateEvent'])->name('panitia.updateEvent');
    });

    Route::get('/api/panitia/events/{id}/detail', [EventController::class, 'detail']);
    Route::get('/panitia/scan/events', [ScanController::class, 'getEventsByUser'])->name('panitia.scan.events');

    Route::get('/panitia/scan', [ScanController::class, 'index'])->name('panitia.scan');
    Route::post('/panitia/scan', [ScanController::class, 'scanQrCode'])->name('panitia.scan.process');
    Route::get('/panitia/events/{eventId}/sessions', [ScanController::class, 'getEventSessions'])->name('panitia.event.sessions');

    Route::get('/panitia/sertifikat', [SertifikatController::class, 'index'])->name('panitia.sertifikat');
    Route::get('/panitia/sertifikat/peserta/{eventId}/{sesiId}', [SertifikatController::class, 'getPesertaByEventSession'])->name('panitia.sertifikat.peserta');
    Route::post('/panitia/sertifikat/upload', [SertifikatController::class, 'upload'])->name('panitia.sertifikat.upload');
    Route::get('/panitia/sertifikat/view/{filename}', [SertifikatController::class, 'viewSertifikat'])->name('panitia.sertifikat.view');
    Route::get('/panitia/sertifikat/download/{filename}', [SertifikatController::class, 'downloadSertifikat'])->name('panitia.sertifikat.download');
    Route::get('/panitia/dashboard/data', [EventController::class, 'getDashboardData'])->name('panitia.dashboardData');
});


// Keuangan routes
Route::middleware([CekRoleDanStatus::class . ':4'])->group(function () {
    Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.dashboard');
    Route::get('/keuangan/list', [KeuanganController::class, 'showList'])->name('keuangan.show');
    Route::get('/keuangan/registrations', [KeuanganController::class, 'getRegistrations'])->name('keuangan.registrations');
    Route::put('/keuangan/registrations/{registrationId}/status', [KeuanganController::class, 'updateRegistrationStatus'])->name('keuangan.updateStatus');
});

// Admin routes
Route::middleware([CekRoleDanStatus::class . ':3'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/register', [RegisterAdminController::class, 'index'])->name('admin.register');
    Route::post('/admin/register', [RegisterAdminController::class, 'store'])->name('admin.register.store');
    Route::get('/admin/pengelolaan-akun', [AdminAkunController::class, 'index'])->name('admin.akuns');
    Route::put('/admin/{id}', [AdminAkunController::class, 'update'])->name('admin.pengguna.update');
    Route::patch('/admin/{id}/status', [AdminAkunController::class, 'toggleStatus'])->name('admin.pengguna.status');
    Route::delete('/admin/{id}', [AdminAkunController::class, 'destroy'])->name('admin.pengguna.destroy');
});

// Member routes
Route::middleware([CekRoleDanStatus::class . ':1'])->group(function () {
    Route::get('/member', [MemberController::class, 'index'])->name('member.dashboard');
    Route::get('/events/{id}', [DetailController::class, 'show'])->name('events.show');
    Route::get('/events/{eventId}/tickets', [RegistrasiController::class, 'showTicketForm'])->name('event.purchase');
    Route::post('/events/{eventId}/tickets', [RegistrasiController::class, 'processRegistration'])->name('event.process');
    Route::get('/tickets/success', [RegistrasiController::class, 'showSuccessPage'])->name('event.success');
    Route::get('/my-tickets', [RegistrasiController::class, 'showTicketHistory'])->name('event.history');
    Route::get('/tickets/{registrationId}/reupload', [RegistrasiController::class, 'showReuploadForm'])->name('event.reupload.form');
    Route::post('/tickets/{registrationId}/reupload', [RegistrasiController::class, 'reuploadPaymentProof'])->name('event.reupload.process');
    Route::get('/certificate/download/{registrationId}', [RegistrasiController::class, 'downloadCertificate'])->name('certificate.download');
});