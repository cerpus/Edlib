<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LtiToolController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LtiController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\LtiValidatedRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'check')->name('login_check');
    Route::post('/log-out', 'logout')->name('log_out');
});

Route::controller(ContentController::class)->group(function () {
    Route::get('/content', 'index')->name('content.index');

    Route::get('/content/mine', 'mine')
        ->middleware('auth')
        ->name('content.mine');

    Route::get('/content/{content}', 'show')
        ->name('content.preview')
        ->whereUlid('content')
        ->can('view', 'content');

    Route::get('/content/create', 'create')->name('content.create');

    Route::post('/content/{content}/copy', 'copy')
        ->can('copy', 'content')
        ->name('content.copy');

    Route::get('/content/{content}/edit', 'edit')
        ->name('content.edit')
        ->whereUlid('content');

    Route::get('/content/create/{tool}', 'launchCreator')
        ->name('content.launch-creator')
        ->whereUlid('tool');

    Route::post('/lti/1.1/item-selection-return', 'store')
        ->middleware(LtiValidatedRequest::class)
        ->middleware('lti.launch-type:ContentItemSelection')
        ->name('content.store');
});

Route::controller(LtiController::class)
    ->middleware(LtiValidatedRequest::class)
    ->prefix('/lti/1.1')
    ->group(function () {
        Route::post('/select', 'select')
            ->middleware('lti.launch-type:ContentItemSelectionRequest')
            ->name('lti.select');
    });

Route::controller(UserController::class)->group(function () {
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'store');

    Route::middleware('auth:web')->group(function () {
        Route::get('/preferences', 'preferences')->name('user.preferences');
        Route::post('/preferences', 'savePreferences')->name('user.save-preferences');
    });
});

Route::middleware('can:admin')->prefix('/admin')->group(function () {
    Route::get('', [AdminController::class, 'index'])->name('admin.index');

    Route::post('/rebuild-content-index', [AdminController::class, 'rebuildContentIndex'])
        ->name('admin.rebuild-content-index');

    Route::controller(LtiToolController::class)->group(function () {
        Route::get('/lti-tools', 'index')->name('admin.lti-tools.index');
        Route::get('/lti-tools/add', 'add')->name('admin.lti-tools.add');
        Route::post('/lti-tools', 'store')->name('admin.lti-tools.store');
    });
});
