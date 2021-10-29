#!/bin/bash

chown -R www-data:www-data /app/bootstrap
/usr/sbin/apache2ctl -DFOREGROUND