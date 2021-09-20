<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait UuidKey
{
    protected static function bootUuidKey(): void
    {
        static::creating(static function (Model $model) {
            $key = $model->getKeyName();
            if (!isset($model->{$key})) {
                $model->{$key} = Str::uuid()->toString();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
