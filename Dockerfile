# 1. Use the official PHP-FPM image as the base
FROM php:8.3-fpm-alpine

# Set the working directory inside the container's web root
WORKDIR /var/www/html

# --- 2. Install Nginx and required dependencies (like 'bash' for scripts) ---
RUN apk update && apk add --no-cache \
    nginx \
    bash \
    && rm -rf /var/cache/apk/*

# --- 3. CRITICAL FIX: Create a symbolic link (shim) for the platform's execution engine ---
# The platform is hard-coded to look for the executable at /usr/sbin/php-fpm.
# This command creates a link so that when the platform calls the WRONG path, 
# it actually runs the program from the CORRECT location: /usr/local/sbin/php-fpm.
# This directly fixes the "no such file or directory" error.
RUN ln -s /usr/local/sbin/php-fpm /usr/sbin/php-fpm

# --- 4. Prepare Configuration Files (Matching platform's expected locations) ---
# Based on the error log, the platform may also be overriding config paths.
# We will create the expected directory and copy your configs into both the standard 
# and the platform-specific locations.
RUN mkdir -p /workspace/nginx/
# Assuming your local configs are in a folder named 'nginx/':
COPY nginx/nginx.conf /etc/nginx/http.d/default.conf
COPY nginx/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
# Copy configs to the location the platform *seems* to expect (for safety)
COPY nginx/nginx.conf /workspace/nginx/nginx.conf
COPY nginx/php-fpm.conf /workspace/nginx/php-fpm.conf

# --- 5. Copy Application Code ---
# This copies your website files into the Nginx web root
COPY . .

# Expose Nginx port 80 and PHP-FPM port 9000
EXPOSE 80 9000

# --- 6. The Final CMD ---
# Since the platform appears to inject its own startup script for both services, 
# we set a simple CMD that just runs the Nginx daemon. The platform should now 
# be able to successfully execute its prepended PHP-FPM command (because the path 
# is fixed) and then proceed to the Nginx startup.
CMD ["nginx", "-g", "daemon off;"]