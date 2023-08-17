<?php

// Routes that should not start sessions

use App\Http\Controllers\ContentController;
use App\Http\Controllers\OembedController;
use Illuminate\Support\Facades\Route;

Route::get('/oembed')
    ->uses(OembedController::class)
    ->name('oembed');

Route::get('/sitemap.xml')
    ->uses([ContentController::class, 'sitemap'])
    ->name('sitemap');
