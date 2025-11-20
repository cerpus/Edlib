<?php

declare(strict_types=1);

namespace App;

use App\Observers\AuditLogObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([AuditLogObserver::class])] // Set user id/name
class AuditLog extends Model
{
    use HasUlids;

    protected $dateFormat = 'Y-m-d H:i:s.u';
    public const string|null UPDATED_AT = null;

    public static function log(string $action, string $content, string|null $userId = null, string|null $userName = null): bool
    {
        $audit = new self();
        $audit->action = $action;
        $audit->content = $content;
        $audit->user_id = $userId;
        $audit->user_name = $userName;

        return $audit->save();
    }
}
