<?php

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KlientiController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;

// =======================
// Faqja kryesore (public)
// =======================
Route::get('/', function () {
    return view('welcome');
});

// =======================
// Login & Logout
// =======================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// =======================
// Rrugët për USER (Punëtor)
// =======================
Route::middleware(['auth', 'role:user'])->group(function () {

    // Krijimi dhe menaxhimi i klientëve
    Route::get('/brillants/create', [KlientiController::class, 'A']);
    Route::post('/brillants', [KlientiController::class, 'B'])->name('klientet.store');
  
    Route::get('/klientet', [KlientiController::class, 'C']);
    Route::get('/klientet/{id}/edit', [KlientiController::class, 'edit']);
    Route::put('/klientet/{id}', [KlientiController::class, 'update']);
    Route::delete('/klientet/{id}', [KlientiController::class, 'destroy'])->name('klientet.destroy');
    Route::get('/klientet/{id}/invoice', [KlientiController::class, 'invoice'])->name('klient.invoice');

    // Dashboard për punëtorin
    Route::get('/user/dashboard', function () {
        $klientet = \App\Models\Klienti::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->get();
        return view('user.dashboard', compact('klientet'));
    })->name('user.dashboard');
});

// =======================
// Rrugët për ADMIN
// =======================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard për adminin
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Menaxhimi i punëtorëve
    Route::get('/admin/punetoret/krijo', [AdminController::class, 'createWorker'])->name('admin.punetoret.create');
    Route::post('/admin/punetoret', [AdminController::class, 'storeWorker'])->name('admin.punetoret.store');
    Route::delete('/admin/punetoret/{id}', [AdminController::class, 'destroy'])->name('admin.punetoret.destroy');

    // Pagesat e punëtorëve
    Route::get('/admin/punetoret/{id}/pagesa', [AdminController::class, 'showPaymentForm'])->name('admin.punetoret.pagesa.form');
    Route::post('/admin/punetoret/{id}/pagesa', [AdminController::class, 'storePayment'])->name('admin.punetoret.pagesa.store');
    Route::put('/admin/punetoret/{id}/update-salary', [AdminController::class, 'updateSalary'])->name('admin.punetoret.update.salary');

    // Faturat & kërkimi
    Route::get('/admin/punetoret/{id}/invoice', [AdminController::class, 'invoice'])->name('admin.punetoret.invoice');
    Route::get('/admin/faturat/{muaji}', [AdminController::class, 'faturatPerMuaj'])->name('admin.fatura.muaji');
    Route::get('/admin/kerko-klient', [AdminController::class, 'kerkoKlient'])->name('admin.kerko.klient');
});



