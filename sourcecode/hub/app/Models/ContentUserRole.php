<?php

declare(strict_types=1);

namespace App\Models;

enum ContentUserRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Reader = 'reader';
}
