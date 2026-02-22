#!/usr/bin/env bash
set -e

echo "Running Laravel setup..."

php artisan config:clear || true
php artisan cache:clear || true

php artisan migrate --force || true
php artisan storage:link || true

echo "Starting Apache..."
apache2-foreground