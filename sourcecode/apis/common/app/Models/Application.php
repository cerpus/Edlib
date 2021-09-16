<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class Application extends Model
{
    protected $keyType = 'uuid';

    public $fillable = [
        'name',
    ];

    public function accessTokens(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
