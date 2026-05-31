FROM php:8.3-fpm

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libsqlite3-dev \
        libcurl4-openssl-dev \
    && docker-php-ext-install curl pdo_mysql pdo_pgsql pdo_sqlite \
    && pecl install redis apcu mongodb xdebug \
    && docker-php-ext-enable redis apcu mongodb xdebug \
    && mkdir -p /usr/local/etc/php/conf.d \
    && echo "apc.enable_cli=1" > /usr/local/etc/php/conf.d/apcu.ini \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

