#!/bin/bash
set -eux

cd /app

mkdir -p storage/app storage/app/public storage/logs storage/framework storage/framework/cache storage/framework/cache/data storage/framework/sessions storage/framework/views
vendor/bin/phpunit -c phpunit.xml --log-junit /app/storage/test-report.xml
