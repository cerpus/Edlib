<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperMaintenanceMode
 */
class MaintenanceMode extends Model
{
    protected $table = 'maintenance_mode';

    protected $casts = [
        'enabled' => 'bool',
    ];

    protected $fillable = [
        'enabled',
    ];

    protected $attributes = [
        'enabled' => false,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'DESC');
        });
    }
}
