#!/bin/bash

set -eux

rm -rf /storage/*
mkdir -p /storage/config /storage/storage /storage/storage/app /storage/storage/logs /storage/storage/framework /storage/storage/framework/cache /storage/storage/framework/sessions /app/storage /app/storage/framework /app/storage/framework/views /storage/storage/app/images /storage/storage/app/fonts

rm -f /storage/test-report.xml
cd /app
vendor/bin/phpunit -c phpunit.xml -d memory_limit=2048M --log-junit /storage/test-report.xml  --cache-result-file /dev/null

if [ ${DUMP_LOG:="false"} = "true" ]; then
  cat storage/logs/laravel.log
fi
