# Use the official Debian-based PHP image with FPM
FROM php:8.2-fpm-bullseye

# 1. Install necessary dependencies (Nginx and basic tools)
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    nano \
    # Install PHP extensions for database connectivity
    && docker-php-ext-install mysqli pdo pdo_mysql \
    # Clean up APT lists to reduce image size
    && rm -rf /var/lib/apt/lists/*

# 2. Configure PHP-FPM: Copy the custom www.conf pool configuration
# *** IMPORTANT: Source path changed to 'nginx/php-fpm.conf' to match platform expectation ***
COPY nginx/php-fpm.conf /etc/php/8.2/fpm/pool.d/www.conf

# 3. Configure Nginx: Copy the custom server block configuration
RUN mkdir -p /etc/nginx/conf.d/
# *** IMPORTANT: Source path changed to 'nginx/nginx.conf' to match platform expectation ***
COPY nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Remove the default Nginx configuration file if it exists, to prevent conflicts
RUN rm -f /etc/nginx/sites-enabled/default

# 4. Copy the entire PHP application source code (from the old directory)
# This assumes your main app files (index.php, etc.) are still in php_files/
COPY php_files/ /var/www/html/

# 5. Set working directory to the webroot
WORKDIR /var/www/html

# 6. Define the start command using a script to run both Nginx and PHP-FPM
CMD service php8.2-fpm start && nginx -g "daemon off;"