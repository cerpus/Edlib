#!/bin/sh
set -e

composer install
chmod -R o+w bootstrap/cache storage

exec "$@"
