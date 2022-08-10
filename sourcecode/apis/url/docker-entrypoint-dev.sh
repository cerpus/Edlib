#!/bin/sh
set -e

update-ca-certificates
yarn
yarn migrate

exec "$@"
