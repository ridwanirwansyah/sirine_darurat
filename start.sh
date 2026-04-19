#!/bin/sh
set -e

echo ">>> Membuat symlink storage..."
php artisan storage:link --force

echo ">>> Menjalankan migrasi database..."
php artisan migrate --force

echo ">>> Membersihkan & membuild cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> Menjalankan PHP-FPM di background..."
php-fpm -D

echo ">>> Menjalankan Nginx..."
nginx -g "daemon off;"