FROM dunglas/frankenphp:1.13.3-php8.4-alpine

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions pdo_mysql zip \
    && apk add --no-cache unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini $PHP_INI_DIR/conf.d/99-custom.ini

ENV SERVER_NAME=:80