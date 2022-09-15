<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 08.08.17
 * Time: 13:12
 */

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

class SessionTestController
{
    public function setValue($id, Request $request)
    {
        $value = $request->input("value");
        $session = $request->session();
        $session->put($id, $value);
        $session->save();
        return [
            "id" => $id,
            "value" => $value
        ];
    }
    public function getValue($id, Request $request)
    {
        return [
            "id" => $id,
            "value" => $request->session()->get($id)
        ];
    }
}
