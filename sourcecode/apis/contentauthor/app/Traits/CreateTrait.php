<?php

namespace App\Traits;

use Illuminate\Support\Collection;

/**
 * Trait CreateTrait
 * @package App\Traits
 */
trait CreateTrait
{

    public $wasRecentlyCreated = false;
    private $isDirty = false;

    /**
     * @param mixed $attributes
     * @return CreateTrait
     */
    public static function create($attributes = null)
    {
        $self = new self();
        if (is_null($attributes)) {
            return $self;
        }
        $properties = get_object_vars($self);
        if (!is_array($attributes)) {
            $arguments = func_get_args();
            $propertiesKeys = array_keys($properties);
            $attributes = [];
            foreach ($arguments as $index => $value) {
                $property = $propertiesKeys[$index];
                $attributes[$property] = $value;
            }
        }
        foreach ($attributes as $attribute => $value) {
            if (!$self->isGuarded($attribute) && array_key_exists($attribute, $properties)) {
                $self->isDirty = $self->isDirty || $self->$attribute !== $value;
                if ($attribute !== 'isDirty') {
                    $self->$attribute = $value;
                }
            }
        }

        return $self;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    private function isGuarded($attribute)
    {
        if (strtolower($attribute) === 'guarded') {
            return true;
        }
        return !empty($this->guarded) && in_array($attribute, $this->guarded);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $returnArray = [];
        $properties = get_object_vars($this);
        foreach ($properties as $property => $value) {
            if( $this->includeProperty($property) !== true){
                continue;
            }
            if ($this->$property instanceof Collection) {
                $returnArray[$property] = $this->$property->map(function ($element) {
                    if (method_exists($element, "toArray")) {
                        return $element->toArray();
                    } else {
                        return $element;
                    }
                })->toArray();
            } else {
                $returnArray[$property] = $value;
            }
        }
        return $returnArray;
    }

    public function includeProperty($property)
    {
        return !in_array(strtolower($property), [
            'isdirty',
            'wasrecentlycreated'
        ]);
    }

    /**
     * @return bool
     */
    public function isDirty()
    {
        if( $this->isDirty === true){
            return true;
        }

        $self = new self();
        return $self->toJson() !== $this->toJson();
    }

    /**
     * @return string
     */
    public function toJson()
    {
        $that = $this;
        unset($that->isDirty, $that->wasRecentlyCreated);
        return json_encode($that);
    }

    public function get($name, $default = null)
    {
        return array_key_exists($name, get_object_vars($this)) ? $this->$name : $default;
    }
}