#!/bin/sh
set -ex

update-ca-certificates

exec "$@"
