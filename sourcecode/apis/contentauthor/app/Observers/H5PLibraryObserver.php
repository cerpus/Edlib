<?php

declare(strict_types=1);

namespace App\Observers;

use App\AuditLog;
use App\H5PLibrary;

class H5PLibraryObserver {
    public function created(H5PLibrary $library): void
    {
        AuditLog::log(
            'Library installed',
            json_encode([
                'id' => $library->id,
                'name' => $library->getLibraryString(true),
                'runnable' => $library->runnable,
            ])
        );
    }

    public function updated(H5PLibrary $library): void
    {
        $changes = [];
        $semanticsChanged = false;
        foreach ($library->getDirty() as $column => $value) {
            if ($column === 'semantics') {
                $semanticsChanged = true;
            } else {
                $changes[$column] = [
                    'from' => $library->getOriginal($column),
                    'to' => $value,
                ];
            }
        }

        AuditLog::log(
            'Library updated',
            json_encode([
                'id' => $library->id,
                'name' => $library->getLibraryString(true),
                'semanticsChanged' => $semanticsChanged,
                'changes' => $changes,
            ])
        );
    }

    public function deleted(H5PLibrary $library): void
    {
        AuditLog::log(
            'Library deleted',
            json_encode([
                'id' => $library->id,
                'name' => $library->getLibraryString(true),
                'runnable' => $library->runnable,
                'patch_version_in_folder_name' => $library->patch_version_in_folder_name,
            ])
        );
    }
}
