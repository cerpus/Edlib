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

    /**
     * Redirect to core.
     *
     * @param  int $id , string $title, string $type, int $score
     * @return Return
     */
    public function redirectToCore($id, $title, $type = '', $score = 0, $redirectToken)
    {
        $returnUrl = $this->getRedirectToCoreUrl($id, $title, $type, $score, $redirectToken);
        if (!is_null($returnUrl)) {
            header('Location: ' . $returnUrl);
            die();
        }
        return;
    }

    public function getRedirectToCoreUrl($id, $title, $type = '', $score = 0, $redirectToken)
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

    private function getCoreBaseUrl($redirectToken) {
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
