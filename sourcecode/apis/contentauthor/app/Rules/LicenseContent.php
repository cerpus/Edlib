<?php

namespace App\Rules;

use App\Http\Libraries\License;
use Illuminate\Contracts\Validation\Rule;

class LicenseContent implements Rule
{
    /**
     * @param string $attribute
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return License::isLicenseSupported($value);
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
