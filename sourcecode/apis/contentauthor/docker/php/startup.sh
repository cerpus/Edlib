#!/bin/sh
set -ex

php artisan migrate --force
