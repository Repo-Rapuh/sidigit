# syntax=docker/dockerfile:1.6

ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm-alpine AS php-base

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/var/www/html/vendor/bin:${PATH}"

WORKDIR /var/www/html

RUN set -eux; \
    apk add --no-cache \
    bash \
    curl \
    git \
    tzdata \
    zip \
    unzip \
    icu \
    libzip \
    oniguruma \
    freetype \
    libpng \
    libjpeg-turbo; \
    apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libpng-dev \
    libjpeg-turbo-dev; \
    docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
    bcmath \
    gd \
    intl \
    opcache \
    pdo_mysql \
    pdo \
    pcntl; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apk del .build-deps; \
    rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/conf.d/error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini
COPY docker/php/entrypoint.sh /usr/local/bin/php-entrypoint

RUN chmod +x /usr/local/bin/php-entrypoint

FROM php-base AS dev

ENV APP_ENV=local

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]

FROM php-base AS build

ENV APP_ENV=production

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY package*.json vite.config.js ./
COPY resources ./resources

RUN apk add --no-cache nodejs npm \
    && npm install \
    && npm run build \
    && npm cache clean --force \
    && rm -rf node_modules \
    && apk del nodejs npm

COPY . .

FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=build /var/www/html /var/www/html

FROM php-base AS production

ENV APP_ENV=production \
    APP_DEBUG=0

COPY --from=build /var/www/html /var/www/html

RUN mkdir -p storage/logs \
    && chown -R www-data:www-data \
    storage \
    bootstrap/cache

ENTRYPOINT ["php-entrypoint"]
CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
