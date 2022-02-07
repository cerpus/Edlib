<?php

declare(strict_types=1);

namespace App\Models;

use App\Edlib\DokuEdlibResource;
use Carbon\Carbon;
use Cerpus\EdlibResourceKit\Contract\EdlibResource;
use Cerpus\EdlibResourceKitProvider\Contracts\ConvertableToEdlibResource;
use Cerpus\EdlibResourceKitProvider\Traits\PublishToEdlib;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Doku extends Model implements ConvertableToEdlibResource
{
    use HasFactory;
    use PublishToEdlib;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
        'data' => 'json',
        'draft' => 'bool',
        'public' => 'bool',
        'edit_allowed_until' => 'datetime',
    ];

    protected $fillable = [
        'data',
        'draft',
        'public',
        'title',
    ];

    protected $attributes = [
        'public' => false,
        'draft' => true,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $self) {
            $self->id ??= Str::uuid()->toString();
            // TODO: use actual user id from auth
            $self->creator_id ??= '123';
            $self->edit_allowed_until ??= Carbon::now()->addMinutes(15);
        });
    }

    public function toEdlibResource(): EdlibResource
    {
        return new DokuEdlibResource(
            $this->id,
            $this->title,
            $this->creator_id,
            $this->public,
            $this->draft,
            $this->created_at->toDateTimeImmutable(),
            $this->updated_at->toDateTimeImmutable(),
        );
    }
}
