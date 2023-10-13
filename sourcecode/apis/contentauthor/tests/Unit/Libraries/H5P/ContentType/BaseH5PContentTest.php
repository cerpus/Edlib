<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries\H5P\ContentType;

use App\Libraries\H5P\ContentType\BaseH5PContent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseH5PContentTest extends TestCase
{
    private BaseH5PContent&MockObject $abstractClass;

    public function setUp(): void
    {
        parent::setUp();

        $this->abstractClass = $this->getMockForAbstractClass(BaseH5PContent::class);
    }

    /** @dataProvider provider_id */
    public function test_id(string $id, bool $useHash, string $expected): void
    {
        $response = $this->abstractClass->setId($id, $useHash);

        $this->assertSame($expected, $response->getId());
    }

    public function provider_id(): \Generator
    {
        yield 'noHash' => ['42', false, '-42'];
        yield 'hash' => ['42', true, '-' . md5('42')];
    }

    public function test_getImportJson(): void
    {
        $response = $this->abstractClass->getImportJson();

        $this->assertObjectHasAttribute('h5p_lib', $response);
        $this->assertObjectHasAttribute('h5p_content_data', $response);
        $this->assertObjectHasAttribute('title', $response);
        $this->assertObjectHasAttribute('content_type', $response);
        $this->assertObjectHasAttribute('nodeId', $response);
        $this->assertObjectHasAttribute('license', $response);
        $this->assertObjectHasAttribute('license', $response->license);

        $this->assertIsObject($response->h5p_content_data);
        $this->assertSame('h5p_content', $response->content_type);
        $this->assertSame('BY', $response->license->license);
    }
}
