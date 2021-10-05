<?php
namespace App\Libraries\NDLA\Notice;

use App\Libraries\OAuthAdapter\OAuthHeaderFactory;
use GuzzleHttp\Client;
use Log;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth1\Signature\Signature;

class Core
{

    private $coreId;

    /**
     * @return mixed
     */
    public function getCoreId()
    {
        return $this->coreId;
    }

    /**
     * @param mixed $coreId
     */
    public function setCoreId($coreId)
    {
        $this->coreId = $coreId;
    }


    /**
     * @RequestMapping(value = "/v1/content/create", method = RequestMethod.POST)
     * @Transactional("transactionManager")
     * public Object pushContent(@ModelAttribute ContentData contentData) {
     * if (contentData.getId() == null || contentData.getTitle() == null || contentData.getType() == null) {
     * return new ResponseEntity<String>(HttpStatus.BAD_REQUEST);
     * }
     */
    /**
     * This does nothing at the moment, but core should be notified about the new content.
     * @param $object
     */
    public function notify($id, $ndlaId, $title, $type)
    {
        $response = false;
        if ($this->notifyCore()) {
            $params = [
                'id' => $id,
                'ndla_id' => $ndlaId,
                'title' => $title,
                'type' => $type
            ];
            $response = $this->doRequest('/v1/content/create', $params, 'POST');
        }

        return $response;
    }

    private function notifyCore()
    {
        return config('ndla.notifyCore', false);
    }

    public function notifyKeywords($json)
    {
        $response = false;
        if ($this->notifyCore()) {
            $params = [
                'json' => json_encode($json),
            ];
            $response = $this->doRequest(sprintf('/v1/content/create/%s/ndladata', $this->getCoreId()), $params,
                'POST');
        }

        return $response;
    }

    private function doRequest($endPoint, $params = [], $method = 'GET')
    {
        $response = new \stdClass();
        try {
            include_once app_path("Libraries/oauth-php/library/OAuthStore.php");
            include_once app_path("Libraries/oauth-php/library/OAuthRequester.php");

            $options = ['consumer_key' => config('core.key'), 'consumer_secret' => config('core.secret')];
            \OAuthStore::instance("2Leg", $options);

            $url = config('core.server') . $endPoint;
            $signatureFactory = new OAuthHeaderFactory(config('core.key'), config('core.secret'));
            $authParams = $signatureFactory->getAuthorizationParameters($method, $url, $params);

            $combinedParams = array_merge($params, $authParams);

            $client = new Client();
            $options = [
                'form_params' => $combinedParams
            ];
            $responseObject = $client->request($method, $url, $options);

            $response = json_decode($responseObject->getBody()->getContents());
            if (!$response) {
                throw new \Exception(__METHOD__ . ': ' . json_last_error() . ': ' . json_last_error_msg(), 501);
            }

        } catch (\Exception $e) {
            throw new \Exception(__METHOD__ . ': ' . $e->getCode() . ': ' . $e->getMessage(), 501);
        }

        return $response;
    }
}