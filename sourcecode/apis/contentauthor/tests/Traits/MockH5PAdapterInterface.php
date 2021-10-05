<?php

namespace Tests\Traits;

use App\Libraries\H5P\Interfaces\H5PAdapterInterface;

trait MockH5PAdapterInterface
{

    public function setupH5PAdapter(array $methods)
    {
        $h5pAdapter = $this->createStub(H5PAdapterInterface::class);
        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof \Closure) {
                $h5pAdapter->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $h5pAdapter->method($method)->willReturn($returnValue);
        }

        app()->instance(H5PAdapterInterface::class, $h5pAdapter);
    }
}
