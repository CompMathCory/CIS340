# Use the official Debian-based PHP image with FPM
FROM php:8.2-fpm-bullseye

# 1. Install necessary dependencies (Nginx and basic tools)
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    nano \
    # Install MySQL/MariaDB client tools for utility/debugging if needed
    mariadb-client \
    # Install PHP extensions for database connectivity
    && docker-php-ext-install mysqli pdo pdo_mysql \
    # Clean up APT lists to reduce image size
    && rm -rf /var/lib/apt/lists/*

# 2. Configure PHP-FPM: Copy the custom www.conf pool configuration
# Source: ./php_files/php-fpm.conf
# Destination: The standard PHP-FPM configuration directory
COPY php_files/php-fpm.conf /etc/php/8.2/fpm/pool.d/www.conf

# 3. Configure Nginx: Copy the custom server block configuration
# We must ensure the destination directory exists before copying
RUN mkdir -p /etc/nginx/conf.d/
# Source: ./php_files/nginx.conf
# Destination: The standard Nginx configuration file
COPY php_files/nginx.conf /etc/nginx/conf.d/default.conf

# Remove the default Nginx configuration file if it exists, to prevent conflicts
RUN rm -f /etc/nginx/sites-enabled/default

# 4. Copy the entire PHP application source code
# Source: ./php_files/
# Destination: The webroot directory inside the container
COPY php_files/ /var/www/html/

# 5. Set working directory to the webroot
WORKDIR /var/www/html

# 6. Define the start command using a script to run both Nginx and PHP-FPM
# This runs PHP-FPM in the background and Nginx in the foreground.
CMD service php8.2-fpm start && nginx -g "daemon off;"