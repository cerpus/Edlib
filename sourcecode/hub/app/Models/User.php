<?php

declare(strict_types=1);

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
        'debug_mode' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'locale',
        'debug_mode',
        'email',
        'google_id',
        'facebook_id',
        'theme',
    ];

    protected $visible = [
        'id',
        'name',
        'admin',
        'created_at',
        'updated_at',
        'locale',
        'debug_mode',
        'theme',
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
        'debug_mode' => false,
    ];

    public function getAuthIdentifierName(): string
    {
        return 'email';
    }
}
