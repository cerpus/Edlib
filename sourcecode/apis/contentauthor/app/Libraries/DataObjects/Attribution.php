<?php

namespace App\Libraries\DataObjects;

class Attribution
{
    /** @var string */
    public $origin = null;
    /** @var array */
    public $originators = [];

    /**
     * @return null
     */
    public function getOrigin()
    {
        return $this->origin;
    }


    public function setOrigin($origin): void
    {
        $this->origin = $origin;
    }


    public function getOriginators(): array
    {
        return $this->originators;
    }


    public function setOriginators(array $originators): void
    {
        $this->originators = $originators;
    }

    /**
     * @param string $name Name of originator
     * @param string $role Role of originator
     */
    public function addOriginator(string $name, string $role): void
    {
        $this->originators[] = (object) [
            'name' => $name,
            'role' => ucfirst(strtolower($role)),
        ];
    }

    public function __toString(): string
    {
        $attributionStrings = [];

        foreach ($this->getOriginators() as $originator) {
            $name = $originator->name;
            if (filter_var($name, FILTER_VALIDATE_URL)) {
                $name = '<a href="' . $name . '" target="_blank">' . $name . '</a>';
            }
            $attributionStrings[] = "{$originator->role}: $name.";
        }

        if ($this->origin) {
            $origin = $this->getOrigin();
            if (filter_var($origin, FILTER_VALIDATE_URL)) {
                $origin = '<a href="' . $origin . '" target="_blank">' . $origin . '</a>';
            }
            $attributionStrings[] = "Originator: $origin.";
        }

        $attributionString = implode(' ', $attributionStrings);

        return $attributionString;
    }
}
