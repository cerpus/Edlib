#!/bin/bash

php artisan clear-compiled
php artisan cache:clear
php artisan route:clear

