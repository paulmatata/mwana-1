#!/bin/sh
set -e

# Render assigns a port via $PORT and expects the container to listen on it.
# Apache defaults to 80, so rewrite both the global port config and the vhost.
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# APP_KEY should normally be set as a Render environment variable (generate once
# locally with `php artisan key:generate --show` and paste the output in). This
# is just a safety net so the app doesn't hard-crash if it was forgotten - but
# sessions/encrypted data won't survive a restart until it's set permanently.
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Generating a temporary one now."
    php artisan key:generate --force
fi

# Aiven's managed MySQL requires SSL. Rather than baking a CA cert file into the
# image (fragile - forgetting it breaks the whole build), paste the cert's PEM
# content into a DB_SSL_CA_CONTENT env var in Render, and this writes it to a
# real file at container startup, then points DB_SSL_CA at it for config/database.php.
if [ -n "$DB_SSL_CA_CONTENT" ]; then
    echo "$DB_SSL_CA_CONTENT" > /tmp/aiven-ca.pem
    export DB_SSL_CA=/tmp/aiven-ca.pem
    echo "Aiven CA certificate written - SSL connections will be verified."
else
    export DB_SSL_VERIFY=false
    echo "WARNING: DB_SSL_CA_CONTENT is not set. Connecting to Aiven without"
    echo "certificate verification. Fine for getting a testing deploy live today -"
    echo "set DB_SSL_CA_CONTENT before this goes in front of real school data."
fi

# Wipe any config cache baked in from a previous build - env vars are only
# known at runtime (Render injects them into the container), never at build time.
php artisan config:clear

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding (idempotent - safe to run on every boot)..."
php artisan db:seed --force

# Cache for request-time performance. Safe to do now since real env vars are
# available at this point (unlike during the Docker build).
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
