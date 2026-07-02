FROM php:8.3-cli-alpine

WORKDIR /var/www

RUN apk add --no-cache \
    bash \
    openssh-client \
    git \
    unzip \
    sqlite \
    sqlite-dev \
    icu-dev \
    libzip-dev \
    libpng-dev \
    mysql-client \
    && docker-php-ext-install intl zip pdo pdo_sqlite pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.json

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

RUN cp .env.example .env \
    && mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache deployments \
    && touch storage/app/tenants.json storage/database.sqlite \
    && chmod -R 775 storage bootstrap/cache deployments \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && php artisan key:generate --force

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]