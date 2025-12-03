#!/bin/bash

# Start PHP-FPM in the background
# This ensures PHP-FPM is ready to process requests on port 9000
echo "Starting PHP-FPM..."
php-fpm

# Start Nginx in the foreground
# Nginx is listening on port 8080 (as defined in nginx.conf)
# The "daemon off;" directive keeps the main process alive, which is critical for Docker
echo "Starting Nginx in foreground..."
exec nginx -g "daemon off;"

# If Nginx fails to start, the script will exit.