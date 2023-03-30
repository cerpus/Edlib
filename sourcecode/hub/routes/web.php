<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LtiToolController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\LoginController;
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
        ->whereUlid('content');

    Route::get('/content/create', 'create')->name('content.create');

    Route::get('/content/{content}/edit', 'edit')
        ->name('content.edit')
        ->whereUlid('content');

    Route::get('/content/create/{tool}', 'launchCreator')
        ->name('content.launch-creator')
        ->whereUlid('tool');
});

Route::middleware('can:admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    Route::controller(LtiToolController::class)->group(function () {
        Route::get('/admin/lti-tools', 'index')->name('admin.lti-tools.index');
        Route::get('/admin/lti-tools/add', 'add')->name('admin.lti-tools.add');
        Route::post('/admin/lti-tools', 'store')->name('admin.lti-tools.store');
    });
});
