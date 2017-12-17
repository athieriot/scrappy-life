FROM php:7.1-cli-alpine

RUN apk add icu icu-libs icu-dev autoconf build-base openssl-dev --no-cache \
    && docker-php-ext-install intl \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apk del autoconf build-base openssl-dev

COPY --from=composer:1.5 /usr/bin/composer /usr/bin/composer

COPY . /usr/src/app
WORKDIR /usr/src/app
RUN composer install --no-dev

ENTRYPOINT [ "bin/scrappy" ]
