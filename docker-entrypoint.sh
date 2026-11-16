#!/bin/sh
set -e

# Map Railway MySQL service variables automatically if DB_HOST is not explicitly provided
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-${MYSQLHOST:-127.0.0.1}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-railway}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-root}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"

export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export CACHE_STORE="${CACHE_STORE:-file}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
fi

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Checking initial seed data..."
USER_COUNT=$(php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo \App\Models\User::count();')
if [ "$USER_COUNT" = "0" ]; then
    echo "==> Seeding initial Sewao demo data..."
    php artisan db:seed --force
fi

echo "==> Starting Sewao server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
