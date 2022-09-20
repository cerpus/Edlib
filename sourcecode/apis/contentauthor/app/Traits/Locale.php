<?php

namespace App\Traits;

trait Locale
{
    protected function getSupportedLocalesAsString()
    {
        return implode(',', array_keys(config('app.supported_locale')));
    }
}
