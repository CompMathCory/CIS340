# 1. Use an official PHP image with Apache
FROM php:8.2-apache

# 2. Install necessary PHP extensions for MySQL (mysqli is what config.php uses)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3. Enable Apache's rewrite module (useful if you expand to use .htaccess)
RUN a2enmod rewrite

# 4. Copy the custom Apache configuration file
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# 5. Copy all your application files from the current directory (GitHub repo) 
#    into the Apache web root (/var/www/html) inside the container
COPY . /var/www/html/

# 6. Set the necessary permissions for the web root
RUN chown -R www-data:www-data /var/www/html

# The base image automatically exposes port 80 and starts Apache.