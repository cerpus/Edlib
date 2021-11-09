<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 22.07.16
 * Time: 13:15
 */

namespace App\Http\Controllers;


class Oauth2AuthenticationController
{
    public function getOauth2Url() {
        $authService = env('OAUTH2_SERVICE', null);
        if (!$authService) {
            return response('Internal Server Error', 500);
        }
        return ['url' => $authService];
    }
    public function checkLogin() {
        return ['status' => 'ok'];
    }
}