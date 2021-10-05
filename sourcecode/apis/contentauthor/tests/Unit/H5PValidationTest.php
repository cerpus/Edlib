<?php

namespace Tests\Unit;

use App\H5PContent;
use App\Libraries\H5P\h5p;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class H5PValidationTest extends TestCase
{
    public function testH5PValidation()
    {
        $h5p = new h5p(DB::connection()->getPdo());
        $h5pContent = new H5PContent();
        $request = new Request();
        $this->assertFalse($h5p->validateStoreInput($request, $h5pContent));

        $request->merge([
            'title' => 'Test',
            'library' => 'SomeLibrary',
            'parameters' => '{}',
        ]);
        $this->assertTrue($h5p->validateStoreInput($request, $h5pContent));

        $request->merge(['parameters' => '']);
        $this->assertFalse($h5p->validateStoreInput($request, $h5pContent));
    }
}
