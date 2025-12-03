# Use the official PHP image as the base
FROM php:8.3-fpm-alpine

# Set the working directory inside the container's web root
WORKDIR /var/www/html

# Install any necessary system tools if needed (e.g., extensions like pdo_mysql)
# RUN docker-php-ext-install pdo_mysql

# Copy the *contents* of your local 'php_files' folder 
# directly into the container's web root (/var/www/html)
COPY . .

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# The default command runs php-fpm
CMD ["php-fpm"]