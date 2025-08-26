FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libzip-dev libpng-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy files
COPY . .

# Install PHP dependencies (ignore gd requirement to prevent failure)
RUN composer install --optimize-autoloader --no-scripts --no-interaction --ignore-platform-req=ext-gd

# Laravel specific: cache config
RUN php artisan config:cache || true

ENV PORT=8000
EXPOSE 8000

# Use shell form so $PORT expands correctly
CMD php artisan serve --host=0.0.0.0 --port=$PORT
