<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property-read string $id
 * @property string $name
 * @property string $login
 * @property bool $admin
 * @property string $upn
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'firstName',
        'lastName',
        'email',
        'isAdmin',
    ];

    protected $casts = [
        'id' => 'string',
        'isAdmin' => 'bool',
    ];

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}
