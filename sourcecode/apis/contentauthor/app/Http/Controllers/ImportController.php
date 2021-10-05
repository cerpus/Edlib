<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use App\Libraries\NDLA\Importers\ImporterBase;
use Illuminate\Http\Request;
use App\Libraries\NDLA\Import;
use Illuminate\Support\Facades\Log;

//use App\Http\Controllers\Controller;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            if (!config('app.enable_ndla_import', false)) {
                $notEnabledResponse = new \stdClass();
                $notEnabledResponse->id = '';
                $notEnabledResponse->report = 'Nah...I dont do that stuff.';

                return response()->json($notEnabledResponse, 418);
            }

            $userName = $request->username;
            $password = $request->password;
            if (($userName !== config('ndla.oeruser')) || ($password !== config('ndla.oerpass'))) {
                $invalidUsernamePasswordResponse = new \stdClass();
                $invalidUsernamePasswordResponse->id = '';
                $invalidUsernamePasswordResponse->report = 'Access denied';

                return response()->json($invalidUsernamePasswordResponse, 403);
            }
            $payload = $request->jsonpayload;
            $import = new Import($payload);
            $import->duplicateAction = $request->get("duplicateaction", ImporterBase::DUPLICATE_SKIP);
            $importResult = $import->import();
        } catch (\Exception $e) {

            $importErrorResponse = new \stdClass();
            $importErrorResponse->id = '';
            $importErrorResponse->report = 'Error importing data: ' . $e->getMessage();
            $importErrorResponse->status = !empty($e->getCode()) ? $e->getCode() : 500;

            Log::emergency(array_merge((array)$importErrorResponse, $request->all()));

            return response()->json($importErrorResponse, $importErrorResponse->status);
        }

        return response()->json($importResult, 201);
    }
}
