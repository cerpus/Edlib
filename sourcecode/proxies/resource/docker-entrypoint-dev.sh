#!/bin/sh
set -e

update-ca-certificates
yarn

exec "$@"
