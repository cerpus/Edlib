<?php

namespace App\Http\Libraries;

class AuthJwtParser
{
    protected function hasNonEmpty($object, $property)
    {
        return property_exists($object, $property) && !empty($object->$property);
    }

    protected function getBestName($identity, $default = 'noname')
    {
        $name = $default;
        if ($this->hasNonEmpty($identity, 'displayName')) {
            $name = $identity->displayName;
        } else if ($this->hasNonEmpty($identity, 'firstName')
            || $this->hasNonEmpty($identity, 'lastName')
        ) {
            if (!empty($identity->firstName)) {
                $names[] = $identity->firstName;
            }
            if (!empty($identity->lastName)) {
                $names[] = $identity->lastName;
            }
            $name = trim(implode(' ', $names));
        }

        return $name;
    }

    protected function getEmail($identity, $default = 'noemail')
    {
        $email = $default;
        if ($this->hasNonEmpty($identity, 'email')) {
            $email = $identity->email;
        }

        return $email;
    }

    protected function getAdmin($identity)
    {
        return $this->hasNonEmpty($identity, 'admin');
    }

    protected function getVerifiedEmails($identity)
    {
        $verifiedEmails = [];

        if ($this->hasNonEmpty($identity, 'email')) {
            $verifiedEmails[] = strtolower($identity->email); // Add primary email
        }

        if ($this->hasNonEmpty($identity, "additionalEmails")) {
            foreach ($identity->additionalEmails as $email) {
                $verifiedEmails[] = strtolower($email);
            }
        }

        return array_unique($verifiedEmails);
    }

}
