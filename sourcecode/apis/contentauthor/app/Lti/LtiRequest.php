<?php

namespace App\Lti;

use Illuminate\Support\Facades\Session;

use function explode;
use function in_array;

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
            ($this->getUserFamilyName() ?? ''),
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

    public function isPreview(): bool
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
            $this->getExtActivityId(),
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

    public function getExtEmbedId(): string|null
    {
        return $this->param('ext_embed_id');
    }

    public function getResourceLinkTitle(): string|null
    {
        return $this->param('resource_link_title');
    }

    public function isAdministrator(): bool
    {
        $roles = explode(',', $this->param('roles') ?? '');

        return in_array('Administrator', $roles);
    }

    public function getEmbedCode(): string|null
    {
        return $this->param('ext_edlib3_embed_code');
    }

    public function getEmbedResizeCode(): string|null
    {
        return $this->param('ext_edlib3_embed_resize_code');
    }

    public function getEnableUnsavedWarning(): bool|null
    {
        $value = $this->param('ext_ca_enable_unsaved_warning');
        return $value !== null ? $value !== '0' : null;
    }

    public function getPublished(): bool|null
    {
        $value = $this->param('ext_edlib3_published');
        if ($value !== null) {
            $value = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        }

        return $value;
    }

    public function getShared(): bool|null
    {
        $value = $this->param('ext_edlib3_shared');
        if ($value !== null) {
            $value = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        }

        return $value;
    }
}
