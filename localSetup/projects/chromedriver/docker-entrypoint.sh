#!/bin/bash
set -e

CACERT=/usr/local/share/ca-certificates/cacert.pem
NSSDB=/home/chromeuser/.pki/nssdb

if [ "$1" = "chromedriver" ]; then
    mkdir -p "$NSSDB"
    certutil -d "sql:$NSSDB" -N --empty-password
    certutil -d "sql:$NSSDB" -A -t "CP,," -n cerpus -i "$CACERT"
    chown -R chromeuser:chromeuser "$(dirname "$NSSDB")"

    cmd="$@"
    set -- su chromeuser -c "$cmd"
fi

exec "$@"
