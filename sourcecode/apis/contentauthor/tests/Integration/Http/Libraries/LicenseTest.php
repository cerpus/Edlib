<?php

namespace Tests\Integration\Http\Libraries;

use App\Http\Libraries\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('licenseProvider')]
    public function testIsLicenseSupported($data): void
    {
        $this->assertSame($data['isSupported'], License::isLicenseSupported($data['license']));
    }

    #[DataProvider('licenseProvider')]
    public function testIsContentCopyable($data): void
    {
        $this->assertSame($data['isCopyable'], License::isContentCopyable($data['license']));
    }

    public static function licenseProvider(): array
    {
        return [
            'PRIVATE' => [[
                'license' => 'PRIVATE',
                'isSupported' => true,
                'isCopyable' => false,
            ]],
            'CC0' => [[
                'license' => 'CC0',
                'isSupported' => true,
                'isCopyable' => true,
            ]],
            'PDM' => [[
                'license' => 'PDM',
                'isSupported' => true,
                'isCopyable' => true,
            ]],
            'BY' => [[
                'license' => 'BY',
                'isSupported' => true,
                'isCopyable' => true,
            ]],
            'BY-SA' => [[
                'license' => 'BY-SA',
                'isSupported' => true,
                'isCopyable' => true,
            ]],
            'BY-NC' => [[
                'license' => 'BY-NC',
                'isSupported' => true,
                'isCopyable' => true,
            ]],
            'BY-ND' => [[
                'license' => 'BY-ND',
                'isSupported' => true,
                'isCopyable' => false,
            ]],
            'BY-NC-SA' => [[
                'license' => 'BY-NC-SA',
                'isSupported' => true,
                'isCopyable' => true,
            ]],
            'BY-NC-ND' => [[
                'license' => 'BY-NC-ND',
                'isSupported' => true,
                'isCopyable' => false,
            ]],
            'EDLL' => [[
                'license' => 'EDLL',
                'isSupported' => true,
                'isCopyable' => false,
            ]],
            'LICENSE' => [[
                'license' => 'LICENSE',
                'isSupported' => false,
                'isCopyable' => true,
            ]],
            'CC1' => [[
                'license' => 'CC1',
                'isSupported' => false,
                'isCopyable' => true,
            ]],
            'SA-BY' => [[
                'license' => 'SA-BY',
                'isSupported' => false,
                'isCopyable' => true,
            ]],
            '_empty_' => [[
                'license' => '',
                'isSupported' => false,
                'isCopyable' => true,
            ]],
        ];
    }

    public function testToH5PLicenseString()
    {
        $this->assertNull(License::toEdLibLicenseString('Junk String')); // Not a license
        $this->assertNull(License::toEdLibLicenseString('by nc private')); // makes no sense; CC and copyright
        $this->assertNull(License::toEdLibLicenseString('GNU GPL')); // not supported by EdLib
        $this->assertNull(License::toEdLibLicenseString('MIT')); // not supported by EdLib

        $this->assertEquals('PRIVATE', License::toEdLibLicenseString('C'));
        $this->assertEquals('PRIVATE', License::toEdLibLicenseString('C- '));
        $this->assertEquals('PRIVATE', License::toEdLibLicenseString('Private'));
        $this->assertEquals('PRIVATE', License::toEdLibLicenseString('Copyright'));

        $this->assertEquals('CC0', License::toEdLibLicenseString('Cc0'));

        $this->assertEquals('BY', License::toEdLibLicenseString('CC BY'));
        $this->assertEquals('BY', License::toEdLibLicenseString('by'));
        $this->assertEquals('BY', License::toEdLibLicenseString('by 4.0'));
        $this->assertEquals('BY', License::toEdLibLicenseString('by-4.0'));

        $this->assertEquals('BY-SA', License::toEdLibLicenseString('by-SA'));
        $this->assertEquals('BY-SA', License::toEdLibLicenseString('CC by-SA'));
        $this->assertEquals('BY-SA', License::toEdLibLicenseString('CC -by-SA'));
        $this->assertEquals('BY-SA', License::toEdLibLicenseString('CC -by -SA  1.0 International'));

        $this->assertEquals('BY', License::toEdLibLicenseString('Attribution'));
        $this->assertEquals('BY-ND', License::toEdLibLicenseString('Attribution-ND 4.0'));
        $this->assertEquals('BY-ND', License::toEdLibLicenseString('Attribution-NoDerivatives'));
        $this->assertEquals('BY-NC-ND', License::toEdLibLicenseString('Attribution-NoNCOMMERCIAL-NoDerivatives 4.0 International'));

        $this->assertEquals('BY-NC-ND', License::toEdLibLicenseString('CC BY-ND-NC'));
        $this->assertEquals('BY-NC-SA', License::toEdLibLicenseString('CC Attribution-SA Noncommercial international 4.0'));

        $this->assertEquals('BY-NC-SA', License::toEdLibLicenseString('Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License'));
        $this->assertEquals('BY-NC', License::toEdLibLicenseString(' Creative Commons Attribution-NonCommercial 4.0 International'));

        $this->assertEquals('PDM', License::toEdLibLicenseString('cc public domain mark'));
        $this->assertEquals('PDM', License::toEdLibLicenseString('pdm'));
        $this->assertEquals('PDM', License::toEdLibLicenseString('pd'));
        $this->assertEquals('PDM', License::toEdLibLicenseString('cc-pd'));
        $this->assertEquals('PDM', License::toEdLibLicenseString('cc - pd'));

        $this->assertEquals('PDM', License::toEdLibLicenseString('CC- pdm'));
        $this->assertEquals('PDM', License::toEdLibLicenseString('Creative-commons public domain mark'));

        $this->assertEquals('CC0', License::toEdLibLicenseString('cc0'));
        $this->assertEquals('CC0', License::toEdLibLicenseString('Zero'));
        $this->assertEquals('CC0', License::toEdLibLicenseString('Creative commons ZeRO'));

        $this->assertEquals('CC PDM', License::toH5PLicenseString('PD'));
        $this->assertEquals('CC BY-ND', License::toH5PLicenseString('Attribution nd'));
        $this->assertEquals('CC0 1.0', License::toH5PLicenseString('cc cc0'));
        $this->assertEquals('C', License::toH5PLicenseString('Private'));
        $this->assertEquals('CC PDM', License::toH5PLicenseString('CC Public domain mark'));
    }

    public function testToEdLibLicenseString()
    {
        $this->assertEquals('BY-SA', License::toEdLibLicenseString('Navngivelse-Del på samme vilkår'));
        $this->assertEquals('BY-NC-SA', License::toEdLibLicenseString('Navngivelse-Sharealike NC'));
        $this->assertEquals('BY-NC', License::toEdLibLicenseString('CC BY ikkeKommersiell 3.0'));
    }
}
