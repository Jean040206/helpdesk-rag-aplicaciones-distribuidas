<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AsistenteRagController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaseConocimientoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::resource('tickets', TicketController::class);

    Route::post('/tickets/{ticket}/asignar', [TicketController::class, 'assign'])
        ->name('tickets.assign');
    Route::post('/tickets/{ticket}/estado', [TicketController::class, 'updateStatus'])
        ->name('tickets.status');
    Route::post('/tickets/{ticket}/seguimiento', [TicketController::class, 'comment'])
        ->name('tickets.comment');
    Route::get('/tickets/{ticket}/evidencias/{evidencia}', [TicketController::class, 'downloadEvidence'])
        ->name('tickets.evidence.download');

    Route::post('/tickets/{ticket}/rag', [AsistenteRagController::class, 'generate'])
        ->middleware('throttle:6,1')
        ->name('tickets.rag.generate');
    Route::post('/tickets/{ticket}/rag/{evaluacion}/decision', [AsistenteRagController::class, 'decide'])
        ->name('tickets.rag.decision');

    Route::get('/rag/evaluaciones', [AsistenteRagController::class, 'index'])
        ->name('rag.index');
    Route::get('/rag/evaluaciones/{evaluacion}', [AsistenteRagController::class, 'show'])
        ->name('rag.show');

    Route::resource('kb', BaseConocimientoController::class)
        ->parameters(['kb' => 'articulo']);

    Route::get('/admin', [AdminController::class, 'index'])
        ->middleware('role:Administrador')
        ->name('admin.index');
});
