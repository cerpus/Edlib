<?php

namespace App\Models;

enum LtiVersion: string
{
    case Lti1_1 = '1.1';

    case Lti1_3 = '1.3';
}
