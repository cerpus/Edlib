<?php
namespace Tests\Feature;

use Tests\TestCase;

class LicensesTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetLicenses()
    {
        $this->withoutMiddleware();

        $this->json('GET', '/v1/licenses')
            ->seeJson(['id' => 'PRIVATE'])
            ->seeJson(['id' => 'CC0'])
            ->seeJson(['id' => 'BY'])
            ->seeJson(['id' => 'BY-SA'])
            ->seeJson(['id' => 'BY-ND'])
            ->seeJson(['id' => 'BY-NC'])
            ->seeJson(['id' => 'BY-NC-SA'])
            ->seeJson(['id' => 'BY-NC-ND'])
            ->seeJson(['id' => 'PDM'])
            ->seeJson(['id' => 'EDLL']);
    }
}
