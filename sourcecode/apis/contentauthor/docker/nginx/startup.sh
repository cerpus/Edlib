#!/bin/sh

mkdir -p /var/log/nginx/healthd
chown -R nginx:nginx /var/log/nginx/healthd
chmod -R 755 /var/log/nginx/healthd
