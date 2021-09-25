<?php

use App\Http\Controllers\ResourceCollaboratorController;
use Illuminate\Support\Facades\Route;

Route::post('/context-resource-collaborators', [ResourceCollaboratorController::class, 'set']);
