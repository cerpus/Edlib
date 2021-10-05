<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 08.08.17
 * Time: 14:42
 */

namespace App\Http\Controllers;


class SessionTestHostController
{
    public function sessionTestPage() {
        return view("safari.sessiontest");
    }
}