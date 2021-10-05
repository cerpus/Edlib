<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 11/30/16
 * Time: 9:54 AM
 */

namespace Tests\Traits;


use App\Http\Libraries\License;
use Cerpus\LicenseClient\Contracts\LicenseContract;

trait MockLicensingTrait
{
    public function setUpLicensing($returnLicense = 'PRIVATE', $copyable = false, $validLicense = true)
    {
        $license = $this->getMockBuilder(License::class)
            ->disableOriginalConstructor()
            ->getMock();

        $licJson = json_decode('[
                {
                    "id": "PRIVATE",
                    "name": "Private"
                },
                {
                    "id": "CC0",
                    "name": "Creative Commons"
                },
                {
                    "id": "BY",
                    "name": "CC Attribution"
                },
                                {
                    "id": "BY-NC",
                    "name": "CC Attribution Noncommercial"
                },
                {
                    "id": "BY-ND",
                    "name": "CC Attribution Noderivatives"
                },
                {
                    "id": "BY-SA",
                    "name": "CC Attribution Sharealike"
                },
                {
                    "id": "EDLL,
                    "name": "Edlib License"
                }
            ]');

        $license->method("getLicenses")->willReturn($licJson);
        $license->method("getLicense")->willReturn($returnLicense);
        $license->method("getOrAddContent")->willReturn(json_decode('{}'));
        $license->method("setLicense")->willReturn(json_decode('{}'));
        $license->method("isContentCopyable")->willReturn($copyable);
        $license->method("isLicenseSupported")->willReturn($validLicense);

        app()->instance(License::class, $license);
    }

    public function setupLicenseContract(array $methods)
    {
        /** @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker $licenseClient */
        $licenseClient = $this->createPartialMock(LicenseContract::class, array_keys($methods));
        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof \Closure) {
                $licenseClient->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $licenseClient->method($method)->willReturn($returnValue);
        }

        app()->instance(LicenseContract::class, $licenseClient);

    }
}
