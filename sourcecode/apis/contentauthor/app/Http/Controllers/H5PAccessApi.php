<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use DB;

class H5PAccessApi extends Controller
{
    public function getAccess($id)
    {
        $db = DB::connection()->getPdo();
        $sql = "SELECT is_private FROM h5p_contents WHERE id=:id";
        $params = [':id' => $id];
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $access = $stmt->fetchAll(\PDO::FETCH_CLASS);
        /* Use this to only return value instead
		$access = $stmt->fetch(\PDO::FETCH_COLUMN); */
        return response()->json($access);
    }
}
