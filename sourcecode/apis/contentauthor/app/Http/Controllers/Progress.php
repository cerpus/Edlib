<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use stdClass;
use Illuminate\Http\Request;
use App\Libraries\H5P\H5PProgress;
use App\Http\Requests\StoreH5PAnswersRequest;

class Progress extends Controller
{
    // Handles only POST
    public function storeProgress(StoreH5PAnswersRequest $request)
    {
        if ($this->hasLogin()) {
            $result = $this->getH5PProgress()->storeProgress($request);

            return response()->json($result);
        } else {
            abort(401, 'Authorization required');
        }
    }

    // Handles GET
    public function getProgress(Request $request)
    {
        $response = new stdClass();
        $response->success = true;

        $data = $this->getH5PProgress()->getProgress($request);
        if ($data !== null) {
            $response->data = $data;
        }

        return response()->json($response);
    }

    private function hasLogin() {
        return Session::get('userId', false) !== false;
    }

    private function getH5PProgress()
    {
        return new H5PProgress(DB::connection()->getPdo(), Session::get('userId', false));
    }
}
