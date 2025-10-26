<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HistoricalRecordController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\NormativaController;
use App\Http\Controllers\EstimacionesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;

Route::middleware('guest')->group(function () {
    // Raíz: lleva al login si no hay sesión
    Route::get('/', function () {
        return redirect()->route('login');
    })->name('root');

    // Login (vista y acción)
    Route::view('/login', 'content.authentications.auth-login-basic')->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1') // 6 intentos por minuto
        ->name('login.post');
});


Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Área autenticada
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard / Inicio
    Route::get('/inicio', [HomeController::class, 'index'])->name('inicio');

    // Vista de perfil de usuario
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('profile.update');
    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('profile.password.update');

    // Redirecciones de cortesía
    Route::redirect('/home', '/inicio', 301)->name('home.redirect');

    // Administración
    Route::middleware(['auth', 'is.admin'])->group(function () {
        Route::resource('usuarios', UserController::class);
    });

    // Registro histórico
    Route::get('/registro-historico', [HistoricalRecordController::class, 'index'])->name('registro-historico');
    Route::get('/registro-historico/pdf', [HistoricalRecordController::class, 'pdf'])->name('registro-historico.pdf');
    Route::get('/registro-historico/pdf/preview', [HistoricalRecordController::class, 'pdfPreview'])
        ->name('registro-historico.pdf.preview');

    // Datos (carga, lotes, confirmaciones)
    Route::prefix('datos')->name('datos.')->group(function () {
        Route::get('/', [DataController::class, 'index'])->name('index');
        Route::post('/', [DataController::class, 'store'])->name('store');

        Route::get('/lotes', [DataController::class, 'lotes'])->name('lotes');
        Route::delete('/lotes/{id}', [DataController::class, 'destroyLote'])
            ->whereNumber('id')
            ->name('lotes.destroy');

        Route::post('/preview/confirm', [DataController::class, 'confirmPreviewImport'])->name('preview.confirm');
        Route::post('/preview/cancel',  [DataController::class, 'cancelPreviewImport'])->name('preview.cancel');
    });

    // Normativa
    Route::get('/normativa', [NormativaController::class, 'index'])->name('normativas.index');

    // Estimaciones
    Route::get('/estimaciones', [EstimacionesController::class, 'index'])->name('estimaciones.index');

    // Acerca del sistema
    Route::view('/acerca-de', 'content.acerca-de.index')->name('acerca-de');

    // Ayuda / Manual de usuario
    Route::view('/ayuda', 'content.ayuda.index')->name('ayuda');
});


Route::fallback(function () {
    return auth()->check()
        ? redirect()->route('inicio') // ->with('warning', 'La ruta no existe.')
        : redirect()->route('login');
});
