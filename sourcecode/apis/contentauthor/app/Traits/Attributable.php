<?php

namespace App\Traits;

use App\ContentAttribution;
use App\Libraries\DataObjects\Attribution;

/**
 * Provides easy access to our current implementation of Attributions.
 */
trait Attributable
{
    public function getAttribution(): Attribution
    {
        return ContentAttribution::firstOrCreate(['content_id' => $this->id], ['attribution' => (new Attribution())])->attribution;
    }

    public function setAttribution(Attribution $attribution)
    {
        ContentAttribution::updateOrCreate(['content_id' => $this->id], ['attribution' => $attribution]);
    }

    public function addAttributionOriginator(string $name, string $role)
    {
        $attribution = $this->getAttribution();

        $attribution->addOriginator($name, $role);

        $this->setAttribution($attribution);
    }

    public function setAttributionOrigin(string $origin)
    {
        $attribution = $this->getAttribution();

        $attribution->setOrigin($origin);

        $this->setAttribution($attribution);
    }

    public function getAttributionAsString(): string
    {
        return (string) $this->getAttribution();
    }

    public function updateAttribution(?string $origin, array $originators)
    {
        $data = new Attribution();
        $data->setOrigin($origin);
        $data->setOriginators(array_values($originators));

        $this->setAttribution($data);
    }
}
