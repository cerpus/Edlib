#!/bin/sh
set -e

chown www-data:www-data /var/www/moodledata

exec "$@"
