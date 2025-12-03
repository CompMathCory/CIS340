# 1. Use the official PHP-FPM image as the base
FROM php:8.3-fpm-alpine

# Set the working directory inside the container's web root
WORKDIR /var/www/html

# --- 2. Install Nginx and clean up ---
# We install Nginx using the Alpine package manager (apk)
RUN apk update && apk add --no-cache \
    nginx \
    # Example: install common extensions if needed
    # && docker-php-ext-install pdo_mysql \ 
    && rm -rf /var/cache/apk/*

# --- 3. Copy Configuration Files ---
# IMPORTANT: This assumes your config files are in a local 'nginx/' folder
COPY nginx/nginx.conf /etc/nginx/http.d/default.conf
COPY nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# --- 4. Copy Application Code ---
# Copy all files from the repository root into the web root
COPY . .

# Expose Nginx port 80 and PHP-FPM port 9000
EXPOSE 80 9000

# --- 5. THE FIX: Combined Startup Command ---
# This CMD line defines the correct entrypoint for the container.
# It uses the correct path for the php-fpm executable: /usr/local/sbin/php-fpm
CMD /usr/local/sbin/php-fpm --fpm-config /usr/local/etc/php-fpm.d/www.conf -D && \
    nginx -g "daemon off;"