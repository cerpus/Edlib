<?php

declare(strict_types=1);

namespace App\Enums;

enum LtiVersion: string
{
    case Lti1_1 = '1.1';

    case Lti1_3 = '1.3';
}
