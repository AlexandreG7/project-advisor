FROM php:8.4-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libsqlite3-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/backend

# Install dependencies first (cached layer)
COPY backend/composer.json backend/composer.lock* ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy source
COPY backend/ .

# Finalize autoloader
RUN composer dump-autoload --optimize \
    && mkdir -p var/cache var/log var/data \
    && chmod -R 777 var/

EXPOSE 9000
CMD ["php-fpm"]
