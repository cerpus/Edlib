<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\DataObjects\Attribution;

class ContentAttribution extends Model
{
    protected $primaryKey = 'content_id';

    protected $fillable = [
        'content_id',
        'attribution',
    ];

    public function getAttributionAttribute($value): Attribution
    {
        if (empty($value)) {
            $value = "{}"; // Default empty object, DB does not accept default values on text fields??
        }

        $deserializedAttribution = json_decode($value);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $deserializedAttribution = new \stdClass(); // We have generated attribution strings to handle as well.
        }

        /** @var Attribution $attributionObject */
        $attributionObject = app(Attribution::class);
        $attributionObject->setOrigin($deserializedAttribution->origin ?? null);
        $attributionObject->setOriginators($deserializedAttribution->originators ?? []);

        return $attributionObject;
    }

    /**
     * @throws \Exception
     */
    public function setAttributionAttribute(Attribution $attribution): void
    {
        $serializedAttribution = json_encode($attribution);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Unable to serialize attribution. Error: ' . json_last_error_msg());
        }

        $this->attributes['attribution'] = $serializedAttribution;
    }
}
