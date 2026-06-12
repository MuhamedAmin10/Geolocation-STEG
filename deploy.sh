#!/bin/bash
set -e

echo "==> Creating SQLite database file if it does not exist..."
mkdir -p database
touch database/database.sqlite

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Caching config and routes for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Pre-deploy setup complete."
