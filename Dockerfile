# Dockerfile

FROM node:20 as build-stage

WORKDIR /var/www

# Copy package.json & install dependencies
COPY package.json package-lock.json ./
RUN npm install

# Copy seluruh project dan build assets
COPY . .
RUN npm run build

# Laravel dependencies
FROM php:8.2-fpm

# Install PHP dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev mariadb-client

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project dari build-stage
WORKDIR /var/www
COPY --from=build-stage /var/www /var/www

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

CMD ["php-fpm"]
