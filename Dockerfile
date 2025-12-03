# Use the official Debian-based PHP image with FPM
FROM php:8.2-fpm-bullseye

# 1. Install dependencies, minimizing layers and using '&&' for reliable cleanup
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        nginx \
        curl \
        nano \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# 2. Configure PHP-FPM: Copy the custom www.conf pool configuration
# This path is now relative to the build context (which starts inside your php_files folder)
COPY nginx/php-fpm.conf /etc/php/8.2/fpm/pool.d/www.conf

# 3. Configure Nginx: Copy the custom server block configuration
RUN mkdir -p /etc/nginx/conf.d/
COPY nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Remove the default Nginx configuration file if it exists, to prevent conflicts
RUN rm -f /etc/nginx/sites-enabled/default

# 4. Copy the entire PHP application source code (including the original php_files/ folder)
COPY php_files/ /var/www/html/

# 5. Set working directory to the webroot
WORKDIR /var/www/html

# 6. Define the start command to run both Nginx and PHP-FPM
# The 'service' command is standard on Debian-based images
CMD service php8.2-fpm start && nginx -g "daemon off;"