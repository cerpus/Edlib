<?php

declare(strict_types=1);

namespace Tests\Unit\Enum;

use App\Enums\ContentRole;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class ContentRoleTest extends TestCase
{
    #[TestDox('Role $role grants $roleToCheck')]
    #[TestWith([ContentRole::Owner, ContentRole::Owner], 'owner, owner')]
    #[TestWith([ContentRole::Owner, ContentRole::Editor], 'owner, editor')]
    #[TestWith([ContentRole::Owner, ContentRole::Reader], 'owner, reader')]
    #[TestWith([ContentRole::Editor, ContentRole::Editor], 'editor, editor')]
    #[TestWith([ContentRole::Editor, ContentRole::Reader], 'editor, reader')]
    #[TestWith([ContentRole::Reader, ContentRole::Reader], 'reader, reader')]
    public function testGrants(ContentRole $role, ContentRole $roleToCheck): void
    {
        $this->assertTrue($role->grants($roleToCheck));
    }

    #[TestDox('Role $role denies $roleToCheck')]
    #[TestWith([ContentRole::Editor, ContentRole::Owner], 'editor does not have owner role')]
    #[TestWith([ContentRole::Reader, ContentRole::Owner], 'reader does not have owner role')]
    #[TestWith([ContentRole::Reader, ContentRole::Editor], 'reader does not have editor role')]
    public function testDenies(ContentRole $role, ContentRole $roleToCheck): void
    {
        $this->assertFalse($role->grants($roleToCheck));
    }
}
