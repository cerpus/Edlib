<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class shareContent implements Rule
{
    private $values = ['share', 'private'];

    /**
     * Determine if the provided 'share' value is valid
     *
     * @param string $attribute
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array(strtolower($value), $this->values);
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
