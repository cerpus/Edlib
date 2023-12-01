<?php

namespace Tests\Integration\Http\Controllers;

use App\H5PContent;
use App\Http\Libraries\License;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentInfoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testList(): void
    {
        $resource1 = H5PContent::factory()->create([
            'id' => 1,
            'created_at' => Carbon::now()->subDays(7),
            'updated_at' => Carbon::now(),
            'license' => License::LICENSE_BY,
        ]);
        $resource2 = H5PContent::factory()->create([
            'id' => 2,
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now(),
            'license' => License::LICENSE_CC,
        ]);
        $resource3 = H5PContent::factory()->create([
            'id' => 3,
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now(),
            'license' => License::LICENSE_EDLIB,
        ]);

        $this->get('v1/content')
            ->assertOk()
            ->assertJson([
                'pagination' => [
                    'totalCount' => 3,
                    'offset' => 0,
                    'limit' => 50,
                ],
                'resources' => [
                    [
                        'externalSystemId' => strval($resource1->id),
                        'title' => $resource1->title,
                        'license' => License::LICENSE_BY,
                    ],
                    [
                        'externalSystemId' => strval($resource2->id),
                        'title' => $resource2->title,
                        'license' => License::LICENSE_CC,
                    ],
                    [
                        'externalSystemId' => strval($resource3->id),
                        'title' => $resource3->title,
                        'license' => License::LICENSE_EDLIB,
                    ],
                ],
            ]);
    }
}
