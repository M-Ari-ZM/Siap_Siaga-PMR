<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

// 1. Public & Guest Routes
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if ($role === 'pmr') return redirect()->route('pmr.dashboard');
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        return redirect()->route('student.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/info', [WebController::class, 'info'])->name('info');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Demo Quick Login Switcher
Route::get('/quick-login', [AuthController::class, 'quickLogin'])->name('auth.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 2. Protected Authenticated Routes
Route::middleware('auth')->group(function () {

    // Shared Emergency Status View & Live Polling API
    Route::get('/emergency/create', [WebController::class, 'createEmergency'])->name('emergency.create');
    Route::post('/emergency', [WebController::class, 'storeEmergency'])->name('emergency.store');
    Route::get('/emergency/{emergency}', [WebController::class, 'showEmergency'])->name('emergency.show');
    Route::get('/emergency/{emergency}/live-status', [WebController::class, 'getEmergencyLiveStatus'])->name('emergency.live-status');

    // Student Specific Routes
    Route::middleware('role:student,pmr,admin')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [WebController::class, 'studentDashboard'])->name('dashboard');
        Route::get('/emergency/create', [WebController::class, 'createEmergency'])->name('emergency.create');
        Route::post('/emergency', [WebController::class, 'storeEmergency'])->name('emergency.store');
        Route::get('/emergency/{emergency}', [WebController::class, 'showEmergency'])->name('emergency.show');
    });

    // PMR Member Specific Routes
    Route::middleware('role:pmr,admin')->prefix('pmr')->name('pmr.')->group(function () {
        Route::get('/dashboard', [WebController::class, 'pmrDashboard'])->name('dashboard');
        Route::post('/availability', [WebController::class, 'toggleAvailability'])->name('availability.toggle');
        Route::post('/location', [WebController::class, 'updatePmrLocation'])->name('location.update');
        Route::post('/emergency/{emergency}/accept', [WebController::class, 'acceptEmergency'])->name('emergency.accept');
        Route::post('/emergency/{emergency}/status', [WebController::class, 'updateEmergencyStatus'])->name('emergency.status');
        Route::get('/emergency/{emergency}/report', [WebController::class, 'showHandlingReportForm'])->name('emergency.report');
        Route::post('/emergency/{emergency}/report', [WebController::class, 'storeHandlingReport'])->name('emergency.report.store');
    });

    // Admin Specific Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
        
        // Master Lokasi Sekolah CRUD
        Route::post('/locations', [\App\Http\Controllers\AdminController::class, 'storeLocation'])->name('locations.store');
        Route::put('/locations/{location}', [\App\Http\Controllers\AdminController::class, 'updateLocation'])->name('locations.update');
        Route::delete('/locations/{location}', [\App\Http\Controllers\AdminController::class, 'destroyLocation'])->name('locations.destroy');

        // Manajemen Anggota PMR & Jadwal Piket
        Route::post('/pmr-members', [\App\Http\Controllers\AdminController::class, 'storePmrMember'])->name('pmr-members.store');
        Route::post('/pmr-members/{user}/toggle-duty', [\App\Http\Controllers\AdminController::class, 'togglePmrDuty'])->name('pmr-members.toggle-duty');

        // Cetak / Export Rekap Kejadian & Penanganan Medis
        Route::get('/export-report', [\App\Http\Controllers\AdminController::class, 'exportReport'])->name('export-report');
    });
});
