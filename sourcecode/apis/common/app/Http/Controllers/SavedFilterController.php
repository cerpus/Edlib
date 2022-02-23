<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavedFilterRequest;
use App\Models\SavedFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class SavedFilterController extends Controller
{
    public function getAllForUser(): Response
    {
        return new JsonResponse(SavedFilter::where('user_id', Auth::user()->id)->with(['choices'])->get()->toArray());
    }

    public function deleteWithId(Request $request, SavedFilter $savedFilter)
    {
        if ($savedFilter->user_id !== Auth::user()->id) {
            abort(403);
        }

        $savedFilter->delete();

        return response()->noContent(200);
    }

    public function createNew(SavedFilterRequest $request): Response
    {
        $savedFilter = SavedFilter::create([
            'name' => $request->get('name'),
            'user_id' => Auth::user()->id,
        ]);

        $savedFilter->choices()->createMany(collect($request->get('choices'))->map(function ($choice) {
            return [
                'group_name' => $choice['group'],
                'value' => $choice['value'],
            ];
        })->values());

        // load choices
        $savedFilter->choices;

        return new JsonResponse($savedFilter->toArray());
    }

    public function updateWithId(Request $request, SavedFilter $savedFilter): Response
    {
        if ($savedFilter->user_id !== Auth::user()->id) {
            abort(403);
        }

        $choices = collect($request->get('choices'))->map(function ($choice) {
            return [
                'group_name' => $choice['group'],
                'value' => $choice['value'],
            ];
        })->values()->toArray();

        foreach ($choices as $choice) {
            if (empty(array_filter($savedFilter->choices->toArray(), function ($dbChoice) use ($choice) {
                return $choice['group_name'] == $dbChoice['group_name'] && $choice['value'] == $dbChoice['value'];
            }))) {
                $savedFilter->choices()->create($choice);
            }
        }

        foreach ($savedFilter->choices as $dbChoice) {
            if (empty(array_filter($choices, function ($choice) use ($dbChoice) {
                return $choice['group_name'] == $dbChoice['group_name'] && $choice['value'] == $dbChoice['value'];
            }))) {
                $dbChoice->delete();
            }
        }

        $savedFilter->fresh();

        return new JsonResponse($savedFilter->fresh('choices')->toArray());
    }
}
