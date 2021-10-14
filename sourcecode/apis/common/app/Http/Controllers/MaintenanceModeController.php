<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceModeRequest;
use App\Models\MaintenanceMode;
use Illuminate\Http\JsonResponse;

final class MaintenanceModeController extends Controller
{
    public function status(): JsonResponse
    {
        return new JsonResponse(MaintenanceMode::firstOrCreate()->toArray());
    }

    public function toggle(MaintenanceModeRequest $request): JsonResponse
    {
        return new JsonResponse(
            MaintenanceMode::create($request->validated())->toArray(),
        );
    }
}
