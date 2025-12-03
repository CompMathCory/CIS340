# 1. Use the official PHP-FPM image as the base
FROM php:8.3-fpm-alpine

# Set the working directory inside the container's web root
WORKDIR /var/www/html

# --- 2. Install Nginx and required dependencies ---
# We install Nginx using the Alpine package manager (apk)
RUN apk update && apk add --no-cache \
    nginx \
    bash \
    && rm -rf /var/cache/apk/*

# --- 3. CRITICAL FIX: Create a symbolic link (shim) for the platform's execution engine ---
# The platform is hard-coded to look for the executable at /usr/sbin/php-fpm.
# This symlink makes the correct executable available at the incorrect path, 
# fixing the "no such file or directory" error from before. [Image of Symbolic Link in Linux]
RUN ln -s /usr/local/sbin/php-fpm /usr/sbin/php-fpm

# --- 4. Prepare Configuration Files (Matching platform's expected locations) ---
# Ensure Nginx config is copied to its standard location and the platform's expected location.
RUN mkdir -p /workspace/nginx/
# Assuming your local configs are in a folder named 'nginx/':
COPY nginx/nginx.conf /etc/nginx/http.d/default.conf
COPY nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
# Copy configs to the location the platform *seems* to expect (for startup command pathing)
COPY nginx/nginx.conf /workspace/nginx/nginx.conf
COPY nginx/php-fpm.conf /workspace/nginx/php-fpm.conf

# --- 5. Copy Application Code ---
# This copies all files from the repository root (e.g., index.php) into the web root
COPY . .

# Expose the port the health check uses (8080) and PHP-FPM (9000)
EXPOSE 8080 9000

# --- 6. The Final CMD ---
# Since the platform likely prepends the PHP-FPM command, we use a simple Nginx command here.
# Nginx should be listening on port 8080 now (as fixed in your nginx.conf file).
CMD ["nginx", "-g", "daemon off;"]