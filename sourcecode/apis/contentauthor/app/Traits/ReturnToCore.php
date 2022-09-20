<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 9/7/16
 * Time: 8:34 AM
 */

namespace App\Traits;

use Illuminate\Support\Facades\Session;

trait ReturnToCore
{
    public function getRedirectToCoreUrl($id, $title, $type, $score, $redirectToken)
    {
        $returnUrl = $this->getCoreBaseUrl($redirectToken);

        if ($returnUrl == null) {
            return;
        }

        $params = [
            'title' => $title,
            'id' => $id,
            'type' => $type,
            'score' => $score,
        ];

        return $returnUrl . '?' . http_build_query($params);
    }

    private function getCoreBaseUrl($redirectToken)
    {
        $redirectKey = 'list.returnUrls.' . $redirectToken;

        if (Session::has($redirectKey)) {
            $returnUrl = Session::get($redirectKey);
            Session::forget($redirectKey);

            return $returnUrl;
        } elseif (Session::has('returnUrl')) {
            $returnUrl = Session::get('returnUrl');
            Session::forget('returnUrl');
        } else {
            return null;
        }
        return $returnUrl;
    }
}
