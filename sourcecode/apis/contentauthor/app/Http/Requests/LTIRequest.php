<?php
namespace App\Http\Requests;

use App\Oauth10\Oauth10Request;
use Illuminate\Support\Facades\Session;

class LTIRequest extends Oauth10Request
{
    private $params;

    public function __construct($requestUri, $params)
    {
        parent::__construct("POST", $requestUri, $params, '');
        $this->params = $params;
    }

    public static function current()
    {
        return app(LTIRequest::class);
    }

    public function getLaunchPresentationReturnUrl()
    {
        return $this->params['launch_presentation_return_url'] ?? null;
    }

    public function param($name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }

    public function getUserId()
    {
        $userId = $this->param("user_id");
        if (is_null($userId)) {
            $userId = Session::get('userId', null);
        }
        return $userId;
    }

    public function getExtUserId()
    {
        return $this->param("ext_user_id");
    }

    public function getUserGivenName()
    {
        return $this->param("lis_person_name_given");
    }

    public function getUserFamilyName()
    {
        return $this->param("lis_person_name_family");
    }

    public function getUserEmail()
    {
        return $this->param("lis_person_contact_email_primary");
    }

    public function getExtModuleId()
    {
        return $this->param("ext_module_id");
    }

    public function getExtModuleName()
    {
        return $this->param("ext_module_name");
    }

    public function getExtActivityId()
    {
        return $this->param("ext_activity_id");
    }

    public function getExtActivityTitle()
    {
        return $this->param("ext_activity_title");
    }

    public function getExtJwtToken() {
        return $this->param("ext_jwt_token");
    }

    public function getToolConsumerInfoProductFamilyCode()
    {
        return $this->param("tool_consumer_info_product_family_code");
    }

    public function getExtContextId()
    {
        return $this->param("context_id");
    }

    public function isPreview()
    {
        return $this->param("ext_preview") === "true";
    }

    public function getLaunchPresentationCssUrl()
    {
        return $this->param("launch_presentation_css_url");
    }

    public function generateContextKey()
    {
        $keys = [
            $this->getToolConsumerInfoProductFamilyCode(),
            $this->getExtContextId(),
            $this->getExtModuleId(),
            $this->getExtActivityId()
        ];
        return sha1(json_encode($keys));
    }

    public function getAllowedLicenses(string $default = "PRIVATE,CC0,BY,BY-SA,BY-NC,BY-ND,BY-NC-SA,BY-NC-ND")
    {
        $allowedLicenses = $this->param("ext_create_content_allowed_licenses");
        if (empty($allowedLicenses)) {
            return $default;
        }

        return $allowedLicenses;
    }

    public function getDefaultLicense($default = 'BY')
    {
        $defaultLicense = $this->param("ext_create_content_default_license");
        return !empty($defaultLicense) ? $defaultLicense : $default;
    }

    public function getLocale()
    {
        return $this->param('launch_presentation_locale');
    }

    public function getExtQuestionSet()
    {
        return $this->param('ext_question_set');
    }

    public function getExtBehaviorSettings()
    {
        return $this->param('ext_behavior_settings');
    }

    public function getExtUseDraftLogic()
    {
        return $this->param('ext_use_draft_logic');
    }

    public function getExtTranslationLanguage()
    {
        return $this->param('ext_translation_language');
    }
}
