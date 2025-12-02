# 1. Use an official PHP image with Apache (most common web server for PHP)
FROM php:8.2-apache

# 2. Install necessary PHP extensions for MySQL (mysqli is what config.php uses)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3. Enable Apache's rewrite module (useful if you expand to use .htaccess)
RUN a2enmod rewrite

# 4. Copy all your application files from the current directory (GitHub repo) 
#    into the Apache web root (/var/www/html) inside the container
COPY . /var/www/html/

# 5. Set the necessary permissions for the web root
RUN chown -R www-data:www-data /var/www/html

# The base image automatically exposes port 80 and starts Apache, so no CMD is needed.