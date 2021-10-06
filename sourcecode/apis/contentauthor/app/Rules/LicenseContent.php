<?php

namespace App\Rules;

use App\Http\Libraries\License;
use Illuminate\Contracts\Validation\Rule;

class LicenseContent implements Rule
{

    private $licenseClient;

    public function __construct(License $license)
    {
        $this->licenseClient = $license;
    }

    /**
     * Determine if the provided 'share' value is valid
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->licenseClient->isLicenseSupported($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.in');
    }
}
