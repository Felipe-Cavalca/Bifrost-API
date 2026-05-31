FROM composer:2 AS composer

FROM php:8.3-cli-alpine

ARG INSTALL_REDIS=0
ARG INSTALL_APCU=0
ARG INSTALL_MYSQL=0
ARG INSTALL_POSTGRESQL=0

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN if [ "$INSTALL_REDIS" = "1" ]; then \
        apk add --no-cache --virtual .redis-build-deps $PHPIZE_DEPS \
        && pecl install redis \
        && docker-php-ext-enable redis \
        && apk del .redis-build-deps; \
    fi \
    && if [ "$INSTALL_APCU" = "1" ]; then \
        apk add --no-cache --virtual .apcu-build-deps $PHPIZE_DEPS \
        && pecl install apcu \
        && docker-php-ext-enable apcu \
        && apk del .apcu-build-deps; \
    fi \
    && if [ "$INSTALL_MYSQL" = "1" ]; then \
        docker-php-ext-install pdo_mysql; \
    fi \
    && if [ "$INSTALL_POSTGRESQL" = "1" ]; then \
        apk add --no-cache libpq-dev \
        && docker-php-ext-install pdo_pgsql; \
    fi

COPY composer.json ./

RUN composer install --no-interaction --no-progress --prefer-dist --no-dev --optimize-autoloader

COPY . .

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
