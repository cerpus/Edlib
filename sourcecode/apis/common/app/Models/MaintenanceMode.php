<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MaintenanceMode
 *
 * @mixin IdeHelperMaintenanceMode
 * @property int $id
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|MaintenanceMode newModelQuery()
 * @method static Builder|MaintenanceMode newQuery()
 * @method static Builder|MaintenanceMode query()
 * @method static Builder|MaintenanceMode whereCreatedAt($value)
 * @method static Builder|MaintenanceMode whereEnabled($value)
 * @method static Builder|MaintenanceMode whereId($value)
 * @method static Builder|MaintenanceMode whereUpdatedAt($value)
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
