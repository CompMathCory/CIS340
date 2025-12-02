# 1. Use an official PHP image with Apache
FROM php:8.2-apache

# 2. Install necessary PHP extensions for MySQL (mysqli is what config.php uses)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3. Enable Apache's rewrite module (useful if you expand to use .htaccess)
RUN a2enmod rewrite

# 4. Copy the custom Apache configuration file
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# 5. ***CRITICAL LINE***: Copy all files from your 'php' subfolder into the server's web root
COPY php/ /var/www/html/

# 6. Set the necessary permissions for the web root
RUN chown -R www-data:www-data /var/www/html