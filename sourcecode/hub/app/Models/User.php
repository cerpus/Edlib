<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'admin' => 'boolean',
    ];

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasManyThrough<Content>
     */
    public function contents(): HasManyThrough
    {
        return $this->hasManyThrough(
            Content::class,
            ContentUser::class,
        );
    }

    /**
     * @return HasOne<UserLogin>
     */
    public function login(): HasOne
    {
        return $this->hasOne(UserLogin::class, 'user_id');
    }
}
