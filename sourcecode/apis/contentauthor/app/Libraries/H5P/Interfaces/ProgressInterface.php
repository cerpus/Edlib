<?php

namespace App\Libraries\H5P\Interfaces;

use Illuminate\Http\Request;

interface ProgressInterface
{
    public function __construct($db, $userId);

    public function storeProgress(Request $request);

    public function getProgress(Request $request);
}
