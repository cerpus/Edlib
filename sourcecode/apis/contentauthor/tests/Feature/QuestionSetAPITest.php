<?php

namespace Tests\Feature;

use Tests\TestCase;

class QuestionSetAPITest extends TestCase
{
    public function testEnsureQuestionsetEndpointIsSecured(): void
    {
        $this->get('/v1/questionsets/')
            ->assertUnauthorized();
    }

    public function testAllowAuthorizedUsersToAccessQuestionsetEndpoint(): void
    {
        $this->withSession(['authId' => 1]);
        $this->get('/v1/questionsets/')
            ->assertOk();
    }
}
