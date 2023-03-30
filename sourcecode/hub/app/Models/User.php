<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
     * @var array<string, mixed>
     */
    protected $attributes = [
        'admin' => false,
    ];

    /**
     * @return HasOne<UserLogin>
     */
    public function login(): HasOne
    {
        return $this->hasOne(UserLogin::class, 'user_id');
    }
}
