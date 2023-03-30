<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Symfony\Component\Uid\Uuid;

use function view;

class LtiLaunch extends Component
{
    /**
     * Uniquely identify the iframe/form pair when multiple are rendered on the
     * same page.
     */
    public readonly string $uniqueId;

    public function __construct(public \App\Lti\LtiLaunch $launch)
    {
        $this->uniqueId = (string) Uuid::v4();
    }

    public function render(): View
    {
        return view('components.lti11-launch');
    }
}
