#!/bin/sh

if [ ! -f '/etc/ssl/private/cerpus.key' ] || [ ! -f '/etc/ssl/private/cerpus.crt' ]; then
    echo 'certificate not setup. Run "./run.sh update-certs" in docker-compose root to create them'
    exit 0
fi

exec "$@"
