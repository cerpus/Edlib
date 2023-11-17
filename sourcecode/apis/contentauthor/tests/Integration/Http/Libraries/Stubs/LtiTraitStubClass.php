<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Libraries\Stubs;

use App\Http\Libraries\LtiTrait;
use App\Lti\Lti;
use Illuminate\Http\Request;

class LtiTraitStubClass
{
    use LtiTrait;

    public function __construct(private readonly Lti $lti)
    {
    }

    public function create(Request $request): string
    {
        return 'create';
    }

    public function doShow(string|int $id, string|null $context, bool|null $preview = false): string
    {
        return 'doShow';
    }

    public function edit(Request $request, string|int $id): string
    {
        return 'edit';
    }
}
