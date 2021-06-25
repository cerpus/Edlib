#!/bin/bash


if [ -d '/cacerts.d' ]; then
    find '/cacerts.d' -type f -print0 | xargs -0 -n 1 -I = keytool -importcert -file = -alias = -cacerts -storepass changeit -noprompt
fi

echo "$@"
exec "$@"
