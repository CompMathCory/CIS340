# 1. Use the official PHP-FPM image as the base
FROM php:8.3-fpm-alpine

# Set the working directory inside the container's web root
WORKDIR /var/www/html

# --- 2. Install Nginx, dependencies, and copy startup script ---
# Install Nginx and Bash (required to run the start.sh script)
RUN apk update && apk add --no-cache \
    nginx \
    bash \
    && rm -rf /var/cache/apk/*

# Copy the new startup script (start.sh) and make it executable
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# --- 3. CRITICAL FIX: Create a symbolic link (shim) for the platform's execution engine ---
# This fixes the original "no such file or directory" error for php-fpm
RUN ln -s /usr/local/sbin/php-fpm /usr/sbin/php-fpm

# --- 4. Prepare Configuration Files ---
# Set up directories and copy configs to both standard and platform-specific paths
RUN mkdir -p /workspace/nginx/
# Assuming your local configs are in a folder named 'nginx/':
COPY nginx/nginx.conf /etc/nginx/http.d/default.conf
COPY nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
# Copy configs to the location the platform *seems* to expect (for startup command pathing)
COPY nginx/nginx.conf /workspace/nginx/nginx.conf
COPY nginx/php-fpm.conf /workspace/nginx/php-fpm.conf

# --- 5. Copy Application Code ---
# This copies all files from the repository root into the web root
COPY . .

# Expose the port the health check uses (8080) and PHP-FPM (9000)
EXPOSE 8080 9000

# --- 6. The Final CMD: Use the shell script to reliably start both services ---
# The start.sh script runs PHP-FPM in the background and Nginx in the foreground.
CMD ["/usr/local/bin/start.sh"]