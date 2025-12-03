# The base image for the runtime environment is php:8.2-fpm-alpine.
# This stage installs dependencies like Composer and necessary PHP extensions.
FROM php:8.2-fpm-alpine AS builder

# Install system dependencies needed for extensions (e.g., git, required build tools)
RUN apk add --no-cache \
    git \
    make \
    gcc \
    g++ \
    autoconf \
    libxml2-dev \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install common PHP extensions (adjust as needed for your specific project)
RUN docker-php-ext-install pdo_mysql opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Set the working directory to the standard web root
WORKDIR /var/www/html

# Copy the application source code from your repository root (where the Dockerfile is)
# The '.' means "everything in the current directory (your repo root)"
# '/var/www/html' is the standard destination for web content
COPY . /var/www/html

# Install PHP dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

# --- Production Stage (NGINX) ---
FROM nginx:alpine

# Copy web files from the builder stage
# /var/www/html contains your application code after composer ran
COPY --from=builder /var/www/html /var/www/html

# Copy the Nginx configuration file
# This assumes you have an nginx.conf file in your repository root.
# If you named it differently, update the source path below.
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port 80 (standard HTTP)
EXPOSE 80

# The default command runs Nginx
CMD ["nginx", "-g", "daemon off;"]