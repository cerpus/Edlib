<?php

return [
    'linear-versioning' => env('FEATURE_LINEAR_VERSIONING', true),
    'licensing' => env('ENABLE_LICENSING', false),
    'content-locking' => env('FEATURE_CONTENT_LOCKING', true),
    'context-collaboration' => env('FEATURE_CONTEXT_COLLABORATION', false),
    'collaboration' => env('FEATURE_COLLABORATION', false),
    'allow-mode-switch' => env('FEATURE_MODE_SWITCH', false),
    "export_h5p_on_save" => env("FEATURE_EXPORT_H5P_ON_SAVE", false),
    "export_h5p_with_local_files" => env("FEATURE_EXPORT_H5P_WITH_LOCAL_FILES", true),
    "lock-max-hours" => env("FEATURE_MAX_LOCKING_HOURS", 24),
    "enable-unsaved-warning" => env("FEATURE_ENABLE_UNSAVED_WARNING", true),
];
