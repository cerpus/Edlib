<?php

$root = realpath(dirname(__DIR__));

/** @var \Composer\Autoload\ClassLoader $autoloading */
$autoloading = require $root . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
unset($root);
