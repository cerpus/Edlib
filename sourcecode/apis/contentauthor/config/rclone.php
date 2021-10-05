<?php

return [
    'rclonePath' => env('RCLONE_PATH', 'rclone'),
    'rcloneConfigPath' => env('RCLONE_CONFIG_PATH', ".rclone.config"),
    'remote' => env("RCLONE_REMOTE", 'openstack:'),
    'checkers' => env('RCLONE_CHECKERS', null),
    'transfers' => env('RCLONE_TRANSFERS', null),
];
