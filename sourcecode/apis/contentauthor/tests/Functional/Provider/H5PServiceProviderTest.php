<?php

declare(strict_types=1);

namespace Tests\Functional\Provider;

use App\Libraries\H5P\Audio\NDLAAudioBrowser;
use App\Libraries\H5P\Image\NDLAContentBrowser;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PImageAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Video\NDLAVideoAdapter;
use App\Libraries\H5P\Video\StreampsAdapter;
use Generator;
use RuntimeException;
use Tests\TestCase;

final class H5PServiceProviderTest extends TestCase
{
    /**
     * @dataProvider ndlaConcreteServiceAndInterfaceProvider
     */
    public function testNdlaServices(string $concrete, string $interface): void
    {
        $this->app->bind(H5PAdapterInterface::class, function () {
            $ndlaAdapter = $this->createMock(H5PAdapterInterface::class);
            $ndlaAdapter->method('getAdapterName')->willReturn('ndla');

            return $ndlaAdapter;
        });

        $this->assertInstanceOf($concrete, $this->app->make($interface));
    }

    public function ndlaConcreteServiceAndInterfaceProvider(): Generator
    {
        yield [NDLAAudioBrowser::class, H5PAudioInterface::class];
        yield [NDLAContentBrowser::class, H5PImageAdapterInterface::class];
        yield [NDLAVideoAdapter::class, H5PVideoInterface::class];
    }

    /**
     * @dataProvider cerpusConcreteServiceAndInterfaceProvider
     */
    public function testCerpusServices(string $concrete, string $interface): void
    {
        $this->app->bind(H5PAdapterInterface::class, function () {
            $ndlaAdapter = $this->createMock(H5PAdapterInterface::class);
            $ndlaAdapter->method('getAdapterName')->willReturn('cerpus');

            return $ndlaAdapter;
        });

        $this->assertInstanceOf($concrete, $this->app->make($interface));
    }

    public function cerpusConcreteServiceAndInterfaceProvider(): Generator
    {
        yield [StreampsAdapter::class, H5PVideoInterface::class];
    }

    /**
     * @dataProvider cerpusUnmappedServiceProvider
     */
    public function testUnmappedCerpusServices(string $interface): void
    {
        $this->app->bind(H5PAdapterInterface::class, function () {
            $ndlaAdapter = $this->createMock(H5PAdapterInterface::class);
            $ndlaAdapter->method('getAdapterName')->willReturn('cerpus');

            return $ndlaAdapter;
        });

        $this->expectException(RuntimeException::class);

        $this->app->make($interface);
    }

    public function cerpusUnmappedServiceProvider(): Generator
    {
        yield [H5PAudioInterface::class];
        yield [H5PImageAdapterInterface::class];
    }
}
