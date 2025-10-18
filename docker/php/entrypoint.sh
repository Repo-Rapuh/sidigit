#!/bin/sh
set -e

APP_DIR="/var/www/html"

mkdir -p "${APP_DIR}/storage/logs" "${APP_DIR}/bootstrap/cache"
touch "${APP_DIR}/storage/logs/laravel.log" "${APP_DIR}/storage/logs/php-error.log"
chown -R www-data:www-data "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

exec "$@"
