FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json ./
RUN npm install
COPY . .
RUN npm run build

FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libsqlite3-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd mbstring pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist --optimize-autoloader

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --no-interaction --no-scripts --optimize \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/tmp/karyalokal.sqlite \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync \
    LOG_CHANNEL=stderr

EXPOSE 10000

CMD ["sh", "-c", "touch \"$DB_DATABASE\" && php artisan migrate --force && exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]