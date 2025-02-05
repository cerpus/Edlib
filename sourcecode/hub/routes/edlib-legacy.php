<?php

declare(strict_types=1);

use App\Http\Controllers\ContentController;
use Illuminate\Support\Facades\Route;

Route::get('/s/resources/{edlib2Content}')
    ->uses([ContentController::class, 'redirectFromEdlib2Id'])
    ->whereUuid('edlib2Content');
