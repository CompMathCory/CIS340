# 1. Use an official PHP FPM image (FPM handles PHP processing)
FROM php:8.2-fpm

# 2. Install necessary system dependencies and PHP extensions for MySQL
RUN apt-get update && \
    apt-get install -y nginx && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    rm -rf /var/lib/apt/lists/*

# 3. Remove default Nginx site config
RUN rm /etc/nginx/conf.d/default.conf

# 4. Copy the custom Nginx configuration file from the 'php_files' directory
COPY php_files/nginx.conf /etc/nginx/conf.d/default.conf

# 5. Copy the custom php-fpm configuration file from 'php_files' (required by platform)
COPY php_files/php-fpm.conf /etc/php/8.2/fpm/pool.d/www.conf

# 6. Set necessary permissions for the web root
RUN mkdir -p /var/www/html && chown -R www-data:www-data /var/www/html

# 7. Copy all your application files from the 'php_files' subfolder into the server's web root
# This path now includes the config files, which is fine.
COPY php_files/ /var/www/html/

# 8. Expose port 80 (Nginx default)
EXPOSE 80

# 9. Start PHP-FPM (for processing PHP) and Nginx (for serving HTTP requests)
CMD service php8.2-fpm start && nginx -g "daemon off;"