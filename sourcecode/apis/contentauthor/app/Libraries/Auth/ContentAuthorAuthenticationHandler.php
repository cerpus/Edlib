<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 06.08.18
 * Time: 11:21
 */

namespace App\Libraries\Auth;


use Cerpus\AuthCore\IdentityResponse;

interface ContentAuthorAuthenticationHandler {
    public function perRequestAuthentication(IdentityResponse $identityResponse);
}