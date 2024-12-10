<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentUserRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Reader = 'reader';
}
