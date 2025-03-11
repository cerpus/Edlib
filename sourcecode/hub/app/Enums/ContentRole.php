<?php

declare(strict_types=1);

namespace App\Enums;

use function in_array;

enum ContentRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Reader = 'reader';

    public function grants(self $role): bool
    {
        return in_array($role, match ($this) {
            ContentRole::Owner => [ContentRole::Owner, ContentRole::Editor, ContentRole::Reader],
            ContentRole::Editor => [ContentRole::Editor, ContentRole::Reader],
            ContentRole::Reader => [ContentRole::Reader],
        }, true);
    }
}
