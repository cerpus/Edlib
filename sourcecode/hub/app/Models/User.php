<?php

namespace App\Models;

use App\Events\UserSaved;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasFactory;
    use HasUlids;

    protected $casts = [
        'admin' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'locale',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => UserSaved::class,
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'admin' => false,
    ];

    public function getAuthIdentifierName(): string
    {
        return 'email';
    }
}
