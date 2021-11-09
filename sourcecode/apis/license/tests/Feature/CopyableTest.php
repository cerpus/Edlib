<?php
namespace Tests\Feature;

use Tests\TestCase;

class CopyableTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testIsCopyable()
    {
        $this->withoutMiddleware();

        $this->json('GET', '/v1/licenses/BY/copyable')
        ->seeJson(['copyable' => true]);

        $this->json('GET', '/v1/licenses/BY-ND/copyable')
            ->seeJson(['copyable' => false])
            ->dontSeeJson(['copyable' => true]);

        $this->json('GET', '/v1/licenses/PRIVATE/copyable')
            ->seeJson(['copyable' => false])
            ->dontSeeJson(['copyable' => true]);

        $this->json('GET', '/v1/licenses/PDM/copyable')
            ->seeJson(['copyable' => true])
            ->dontSeeJson(['copyable' => false]);

        $this->json('GET', '/v1/licenses/EDLL/copyable')
            ->seeJson(['copyable' => false])
            ->dontSeeJson(['copyable' => true]);
    }
}
