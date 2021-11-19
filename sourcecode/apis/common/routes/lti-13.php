<?php

use App\Http\Controllers\Lti13Controller;
use Illuminate\Support\Facades\Route;

Route::post('/oidclogin', [Lti13Controller::class, 'oidcLogin']);
Route::get('/.well-known/jwks.json', [Lti13Controller::class, 'getJwksKeys']);
Route::post('/launch', [Lti13Controller::class, 'launch'])->name('lti.launch');
Route::get('/deep-linking-return', [Lti13Controller::class, 'deepLinkingReturn'])->name('lti.deepLinkingReturn');
