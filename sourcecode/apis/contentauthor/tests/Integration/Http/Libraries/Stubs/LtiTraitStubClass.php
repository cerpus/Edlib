<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Libraries\Stubs;

use App\H5pLti;
use App\Http\Libraries\LtiTrait;
use Illuminate\Http\Request;

class LtiTraitStubClass
{
    use LtiTrait;

    public function __construct(private readonly H5pLti $lti)
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
