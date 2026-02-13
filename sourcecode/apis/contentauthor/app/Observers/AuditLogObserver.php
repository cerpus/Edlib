<?php

declare(strict_types=1);

namespace App\Observers;

use App\AuditLog;

class AuditLogObserver
{
    public function creating(AuditLog $logEntry): void
    {
        if ($logEntry->user_name === '' or $logEntry->user_name === null) {
            $logEntry->user_name = 'Unknown';

            if (app()->runningUnitTests()) {
                $logEntry->user_name = 'Unit Test';
            } elseif (app()->runningInConsole()) {
                $logEntry->user_name = 'Console';
            } elseif (\Session::isStarted()) {
                $logEntry->user_name = \Session::get('name', 'Unknown');
            }
        }
        if (($logEntry->user_id === '' or $logEntry->user_id === null) and \Session::isStarted()) {
            $logEntry->user_id = \Session::get('authId');
        }
    }
}
