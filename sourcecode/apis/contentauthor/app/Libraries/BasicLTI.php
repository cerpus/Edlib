<?php

namespace App\Libraries;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Exception;
use App\Libraries\oAuthLTI\OAuthConsumer;
use App\Libraries\oAuthLTI\OAuthRequest;
use App\Libraries\oAuthLTI\OAuthSignatureMethod_HMAC_SHA1;

class BasicLTI extends Controller
{
    private $launchUrl = '';
    private $returnPoint = '';
    private $resourceLinkId = '';
    private $extraLti = array();
    private $oAuthKey = '';
    private $oAuthSecret = '';
    private $signedFields;

    public function __construct($key = null, $secret = null)
    {
        $this->oAuthKey = $key;
        if (is_null($this->oAuthKey)) {
            $this->oAuthKey = config('auth.cerpus_core.key');
        }

        $this->oAuthSecret = $secret;
        if (is_null($this->oAuthSecret)) {
            $this->oAuthSecret = config('auth.cerpus_core.secret');
        }
    }

    /**
     * Where the Tool provider should redirect to
     *
     * @param string $returnPoint
     */
    public function setReturnPoint($returnPoint)
    {
        $this->returnPoint = $returnPoint;
    }

    /**
     * Unique resource id
     *
     * @param string $resourceLinkId
     */
    public function setResourceLinkId($resourceLinkId)
    {
        $this->resourceLinkId = $resourceLinkId;
    }

    /**
     * The URL to the resource
     *
     * @param $launchUrl
     */
    public function setLaunchUrl($launchUrl)
    {
        $this->launchUrl = $launchUrl;
    }

    /**
     * Set the launch URL by parsing XML file
     *
     * @param string $xmlUrl URL to XML file
     * @return string The launch URL
     * @throws Exception
     */
    public function setLaunchUrlFromXML($xmlUrl)
    {
        $launchUrl = '';
        $xmlString = file_get_contents($xmlUrl);

        if ($xmlString !== false) {
            $parser = xml_parser_create();
            $xmlData = array();
            xml_parse_into_struct($parser, $xmlString, $xmlData);
            xml_parser_free($parser);

            //Locate the 'BLTI:LAUNCH_URL'
            foreach ($xmlData as $item) {
                if (array_key_exists('tag', $item) &&
                    mb_strtoupper($item['tag']) === 'BLTI:LAUNCH_URL' &&
                    array_key_exists('value', $item)
                ) {
                    $launchUrl = $item['value'];
                    break;
                }
            }
        } else {
            throw new Exception('Unable to contact server to get configuration (CB1502)', 1502);
        }
        if ($launchUrl === '') {
            throw new Exception('Could not find launch URL, please check your configuration (CB1503)', 1503);
        }

        $this->setLaunchUrl($launchUrl);

        return $this->launchUrl;
    }

    /**
     * Any extra parameters that should be added to the request
     *
     * @param array $extraLti
     */
    public function setExtraLti($extraLti)
    {
        $this->extraLti = $extraLti;
    }

    /**
     * Set the key/secret pair used to sign the request, default values are read from the environment file
     *
     * @param $key
     * @param $secret
     */
    public function setOAuthConfig($key, $secret)
    {
        $this->oAuthKey = $key;
        $this->oAuthSecret = $secret;
    }

    /**
     * The HTML form with javascript that submits the form
     *
     * @return string HTML
     */
    public function getForm()
    {
        $this->signFields();
        $form = '<!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                </head>
                <body>';
        $form .= '<form action="' . $this->launchUrl . '" id="lti_form" method="POST" accept-charset="UTF-8" style="display:none;">';
        foreach ($this->signedFields as $name => $value) {
            $form .= '<input type="text" name="' . $name . '" value="' . htmlspecialchars($value) . '">';
        }
        $form .= '</form>';

        return $form . '
            <script>
                document.getElementById(\'lti_form\').submit();
            </script>
        </body></html>';
    }

    /**
     * Add the OAuth signature fields
     */
    private function signFields()
    {
        $fields = array(
            'lti_message_type' => 'basic-lti-launch-request',
            'lti_version' => 'LTI-1p0',
            'context_type' => 'CourseSection',
            'launch_presentation_width' => 850,
            'launch_presentation_height' => 500,
            'launch_presentation_return_url' => $this->returnPoint,
            'selection_directive' => true,
            'ext_content_return_types' => "url, image_url, lti_launch_url, iframe, oembed, file",
            'ext_content_return_url' => $this->returnPoint,
            'ext_content_intended_use' => "embed",
            'launch_presentation_document_target' => 'iframe',
            'resource_link_id' => $this->resourceLinkId,
        );

        // Any extra information provided by caller
        foreach ($this->extraLti as $key => $value) {
            $fields[$key] = $value;
        }

        $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
        $consumer = new OAuthConsumer($this->oAuthKey, $this->oAuthSecret, null);
        $acc_req = OAuthRequest::from_consumer_and_token($consumer, '', "POST", $this->launchUrl, $fields);
        $acc_req->sign_request($hmac_method, $consumer, '');

        $this->signedFields = $acc_req->get_parameters();
    }
}
