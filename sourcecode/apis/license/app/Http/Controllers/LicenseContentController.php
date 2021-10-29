<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Content;
use App\Models\ContentLicense;


class LicenseContentController
{
    public function index($license = null)
    {
        if (is_null($license)) {
            return [];
        }

        $contentLicense = DB::table('content_license')
            ->select('content_id as id')
            ->where('license_id', $license)
            ->get();

        $content = Content::whereIn('id',
            array_map(function ($item) {
                return $item->id;
            }, $contentLicense->toArray()))
            ->with('licenses')->get();

        return $content;
        /** Returns something like:
         * [{"id":11,"site":"Content Author","content_id":"6afb88af-339e-467d-9ac4-955a724c9949","name":"Lys, lysstyring og stikkontakter","licenses":[{"name":"CC0"}]}
         */
    }
}