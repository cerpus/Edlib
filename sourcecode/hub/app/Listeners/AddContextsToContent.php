<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ContentSaving;
use App\Models\LtiPlatform;
use Illuminate\Http\Request;

// TODO: only apply upon creation
final readonly class AddContextsToContent
{
    public function __construct(private Request $request) {}

    public function handleSaving(ContentSaving $event): void
    {
        $platform = $this->getLaunchingLtiPlatform();

        if (!$platform) {
            return;
        }

        foreach ($platform->contexts as $context) {
            $event->content->contexts()->attach($context);
        }
    }

    private function getLaunchingLtiPlatform(): LtiPlatform|null
    {
        if (!$this->request->hasPreviousSession()) {
            return null;
        }

        $key = $this->request->session()->get('lti.oauth_consumer_key');

        if ($key === null) {
            return null;
        }

        return LtiPlatform::where('key', $key)->first();
    }
}
