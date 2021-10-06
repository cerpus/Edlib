<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;

class LicenseHelperTraitTest extends TestCase
{
    public function testImportCorrectLicenses()
    {
        $class = new TestClass();

        // We will import all CC licenses now!
        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'by']]));
        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'by-sa']]));
        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'by-nc']]));
        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'by-nc-sa']]));
        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'by-nd']]));
        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'by-nc-nd']]));

        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'pdm']]));
        $this->assertTrue($class->canImport((object)['license' => (object)['license' => 'cc0']]));
    }

    public function testNormalizeCopyrightString()
    {
        $class = new TestClass();

        $this->assertNull($class->toEdLibLicenseString('Junk String')); // Not a license
        $this->assertNull($class->toEdLibLicenseString('by nc private')); // makes no sense; CC and copyright WTF??
        $this->assertNull($class->toEdLibLicenseString('GNU GPL')); // not supported by EdLib
        $this->assertNull($class->toEdLibLicenseString('MIT')); // not supported by EdLib

        $this->assertEquals('PRIVATE', $class->toEdLibLicenseString('C'));
        $this->assertEquals('PRIVATE', $class->toEdLibLicenseString('C- '));
        $this->assertEquals('PRIVATE', $class->toEdLibLicenseString('Private'));
        $this->assertEquals('PRIVATE', $class->toEdLibLicenseString('Copyright'));

        $this->assertEquals('CC0', $class->toEdLibLicenseString('Cc0'));

        $this->assertEquals('BY', $class->toEdLibLicenseString('CC BY'));
        $this->assertEquals('BY', $class->toEdLibLicenseString('by'));
        $this->assertEquals('BY', $class->toEdLibLicenseString('by 4.0'));
        $this->assertEquals('BY', $class->toEdLibLicenseString('by-4.0'));

        $this->assertEquals('BY-SA', $class->toEdLibLicenseString('by-SA'));
        $this->assertEquals('BY-SA', $class->toEdLibLicenseString('CC by-SA'));
        $this->assertEquals('BY-SA', $class->toEdLibLicenseString('CC -by-SA'));
        $this->assertEquals('BY-SA', $class->toEdLibLicenseString('CC -by -SA  1.0 International'));

        $this->assertEquals('BY', $class->toEdLibLicenseString('Attribution'));
        $this->assertEquals('BY-ND', $class->toEdLibLicenseString('Attribution-ND 4.0'));
        $this->assertEquals('BY-ND', $class->toEdLibLicenseString('Attribution-NoDerivatives'));
        $this->assertEquals('BY-NC-ND', $class->toEdLibLicenseString('Attribution-NoNCOMMERCIAL-NoDerivatives 4.0 International'));

        $this->assertEquals('BY-NC-ND', $class->toEdLibLicenseString('CC BY-ND-NC'));
        $this->assertEquals('BY-NC-SA', $class->toEdLibLicenseString('CC Attribution-SA Noncommercial international 4.0'));

        $this->assertEquals('BY-NC-SA', $class->toEdLibLicenseString('Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License'));
        $this->assertEquals('BY-NC', $class->toEdLibLicenseString(' Creative Commons Attribution-NonCommercial 4.0 International'));

        $this->assertEquals('PDM', $class->toEdLibLicenseString('cc public domain mark'));
        $this->assertEquals('PDM', $class->toEdLibLicenseString('pdm'));
        $this->assertEquals('PDM', $class->toEdLibLicenseString('pd'));
        $this->assertEquals('PDM', $class->toEdLibLicenseString('cc-pd'));
        $this->assertEquals('PDM', $class->toEdLibLicenseString('cc - pd'));

        $this->assertEquals('PDM', $class->toEdLibLicenseString('CC- pdm'));
        $this->assertEquals('PDM', $class->toEdLibLicenseString('Creative-commons public domain mark'));

        $this->assertEquals('CC0', $class->toEdLibLicenseString('cc0'));
        $this->assertEquals('CC0', $class->toEdLibLicenseString('Zero'));
        $this->assertEquals('CC0', $class->toEdLibLicenseString('Creative commons ZeRO'));
    }

    public function testH5PLicense()
    {
        $class = new TestClass();

        $this->assertEquals('CC PDM', $class->toH5PLicenseString('PD'));

        $this->assertEquals('CC BY-ND', $class->toH5PLicenseString('Attribution nd'));
        $this->assertEquals('CC0 1.0', $class->toH5PLicenseString('cc cc0'));
        $this->assertEquals('C', $class->toH5PLicenseString('Private'));
        $this->assertEquals('CC PDM', $class->toH5PLicenseString('CC Public domain mark'));
    }

    public function testNaturalLanguage()
    {
        $class = new TestClass();

        $this->assertEquals('BY-SA', $class->toEdLibLicenseString('Navngivelse-Del på samme vilkår'));
        $this->assertEquals('BY-NC-SA', $class->toEdLibLicenseString('Navngivelse-Sharealike NC'));
        $this->assertEquals('BY-NC', $class->toEdLibLicenseString('CC BY ikkeKommersiell 3.0'));
    }
}

class TestClass
{
    use LicenseHelper;
}
