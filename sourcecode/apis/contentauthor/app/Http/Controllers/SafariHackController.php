<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SafariHackController extends Controller
{
    public function index(Request $request)
    {
        $nextRedirect = $request->input('nextRedirect');
        $redirect = $request->input('redirect');

        $request->session()->put('caVisited', true);

        $params = [
            'caVisited' => 'true',
            'redirect' => $nextRedirect
        ];
        $returnUrl = $redirect.'?'.http_build_query($params);

        return redirect($returnUrl)->withCookie('caVisited', '1');
    }

    public function jailBreakDialog(Request $request) {
        $request->session()->put("hackingSafari", "safari");
        return view('safari.hackdialog');
    }
}
