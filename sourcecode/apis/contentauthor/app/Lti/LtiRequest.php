<?php

namespace App\Lti;

use Illuminate\Support\Facades\Session;

class LtiRequest extends \Cerpus\EdlibResourceKit\Oauth1\Request
{
    public function getReturnUrl(): string|null
    {
        return $this->param('launch_presentation_return_url')
            ?? $this->param('content_item_return_url')
            ?? null;
    }

    public function isContentItemSelectionRequest(): bool
    {
        return $this->param('lti_message_type') === 'ContentItemSelectionRequest';
    }

    public function param($name, $default = null)
    {
        return $this->has($name) ? $this->get($name) : $default;
    }

    public function getUserId()
    {
        return $this->param('user_id')
            ?? $this->param('ext_user_id')
            ?? Session::get('userId') // FIXME: i question the wisdom of this
        ;
    }

    /**
     * Get the user's full name if supplied by the LTI platform, or assemble one
     * from the given name and family name.
     */
    public function getUserName(): string|null
    {
        return $this->getUserFullName() ?: trim(
            ($this->getUserGivenName() ?? '') . ' ' .
            ($this->getUserFamilyName() ?? '')
        ) ?: null;
    }

    public function getUserFullName(): string|null
    {
        return $this->param('lis_person_name_full');
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

    public function getExtTranslationLanguage()
    {
        return $this->param('ext_translation_language');
    }

    public function getExtEmbedId(): string|null
    {
        return $this->param('ext_embed_id');
    }

    public function getResourceLinkTitle(): string|null
    {
        return $this->param('resource_link_title');
    }
}
