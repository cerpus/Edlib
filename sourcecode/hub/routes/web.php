<?php

use App\Http\Controllers\Admin\LtiToolController;
use App\Http\Controllers\ExplorerController;
use App\Http\Controllers\LoginController;
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

Route::get('/content-explorer', [ExplorerController::class, 'index']);

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'check')->name('login_check');
    Route::post('/log-out', 'logout')->name('log_out');
});

Route::middleware('can:admin')->controller(LtiToolController::class)->group(function () {
    Route::get('/admin/lti-tools', 'index')->name('admin.lti-tools.index');
    Route::get('/admin/lti-tools/add', 'add')->name('admin.lti-tools.add');
    Route::post('/admin/lti-tools', 'store')->name('admin.lti-tools.store');
});
