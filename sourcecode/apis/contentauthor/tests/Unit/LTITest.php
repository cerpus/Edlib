<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Response;

class LTITest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLtiEndpointExists()
    {
        $this->get('/lti/launch')
            ->assertStatus(Response::HTTP_OK);
    }
}
