<?php
return [
    'versioning' => true,
    'linear-versioning' => env('FEATURE_LINEAR_VERSIONING', false),
    'licensing' => env('ENABLE_LICENSING', false),
    'content-locking' => env('FEATURE_CONTENT_LOCKING', true),
    'context-collaboration' => env('FEATURE_CONTEXT_COLLABORATION', false),
    'add-ext-question-set-to-request' => env('FEATURE_EXT_QUESTION_SET_TO_REQUEST', false),
    'no-rabbitmq' => false,
    'collaboration' => env('FEATURE_COLLABORATION', false),
    'allow-mode-switch' => env('FEATURE_MODE_SWITCH', false),
    'use-add-link-resource' => env('FEATURE_USE_ADD_LINK_RESOURCE', true),
    'enableDraftLogic' => env('FEATURE_ENABLE_DRAFT_LOGIC', true),
    "enable-recommendation-engine" => env("FEATURE_ENABLE_RECOMMENDATION_ENGINE", true),
    "export_h5p_on_save" => env("FEATURE_EXPORT_H5P_ON_SAVE", false),
    "export_h5p_with_local_files" => env("FEATURE_EXPORT_H5P_WITH_LOCAL_FILES", true),
    "lock-max-hours" => env("FEATURE_MAX_LOCKING_HOURS", 24),
];
