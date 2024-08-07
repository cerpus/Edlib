<?php

declare(strict_types=1);

namespace Tests\Integration\Traits\Stubs;

use App\Traits\Versionable;
use Illuminate\Foundation\Testing\WithFaker;

class VersionableStubClass
{
    use Versionable;
    use WithFaker;

    public string $id;
    public string $version_id;

    public function __construct()
    {
        $this->setUpFaker();
        $this->id = $this->faker->uuid;
        $this->version_id = $this->faker->uuid;
    }

    public function getVersionColumn(): string
    {
        return 'version_id';
    }
}
