<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

trait UuidForKey
{
    /**
     * Used by Eloquent to get primary key type.
     * UUID Identified as a string.
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Used by Eloquent to get if the primary key is auto increment value.
     * UUID is not.
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    public static function bootUuidForKey()
    {
        static::creating(function ($model) {
            $model->keyType = 'string';
            $model->incrementing = false;

            /** @var Model $model */
            $primaryKey = $model->getKeyName();
            if ($model->isFillable($primaryKey) === false || empty($model->{$primaryKey})) {
                $model->{$primaryKey} = Uuid::uuid4()->toString();
            }
        });
    }
}
