<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Response;

class QuestionSetAPITest extends TestCase
{
    /**
     * Ensure the questionsets endpoint is secured
     * https://jira.cerpus.com/browse/CCC-3184
     */
    public function test_CCC_3184()
    {
        $this->get(route('api.get.questionsets'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->withSession(['authId' => 1]);
        $this->get(route('api.get.questionsets'))
            ->assertStatus(Response::HTTP_OK);

        session()->flush();

        $this->get(route('api.get.questionsets'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
