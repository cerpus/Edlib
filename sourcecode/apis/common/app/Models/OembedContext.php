<?php

namespace App\Models;

use App\Models\Traits\UuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OembedContext extends Model
{
    use HasFactory;
    use UuidKey;

    protected $hidden = [
        'jwt',
    ];

    protected $fillable = [
        'jwt',
    ];
}
