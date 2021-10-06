<?php

namespace App\Http\Controllers\API;

use App\H5PContent;
use App\Http\Controllers\Controller;

class H5PTypeApi extends Controller
{
    public function getTypes($ids)
    {
        $h5pTypes = H5PContent::with('library:id,name')
            ->select('id', 'library_id')
            ->whereIn('id', explode(',', $ids))
            ->orderBy('id')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type['id'],
                    'type' => $type->library['name']
                ];
            });

        return response()->json($h5pTypes);
    }
}
