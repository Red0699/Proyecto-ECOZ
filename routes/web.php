<?php

use Illuminate\Support\Facades\Route;

// Controladores de la plantilla Materio
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\RiIcons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;

// Tus controladores
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HistoricalRecordController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\NormativaController;

// Login 
Route::get('/login', function () {
    return view('content.authentications.auth-login-basic');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- GRUPO PARA USUARIOS AUTENTICADOS ---
Route::middleware(['auth'])->group(function () {

    Route::get('/inicio', [\App\Http\Controllers\HomeController::class, 'index'])->name('inicio');


    Route::middleware(['auth', 'is.admin'])->group(function () {
        Route::resource('usuarios', UserController::class);
    });

    Route::get('/registro-historico', [HistoricalRecordController::class, 'index'])->name('registro-historico');

    Route::get('/datos', [DataController::class, 'index'])->name('datos.index');
    Route::post('/datos', [DataController::class, 'store'])->name('datos.store');

    Route::get('/datos/lotes', [DataController::class, 'lotes'])->name('datos.lotes');
    Route::delete('/datos/lotes/{id}', [DataController::class, 'destroyLote'])->name('datos.lotes.destroy');

    Route::post('/datos/preview/confirm', [DataController::class, 'confirmPreviewImport'])->name('datos.preview.confirm');
    Route::post('/datos/preview/cancel',  [DataController::class, 'cancelPreviewImport'])->name('datos.preview.cancel');
    Route::get('/normativa', [NormativaController::class, 'index'])
        ->name('normativas.index');
});
