<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SingleLogoutController extends Controller
{
    /**
     * Log user out and redirect
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        Session::flush();
        $returnUrl = $request->get('returnUrl', null);

        return redirect($returnUrl);
    }

}
