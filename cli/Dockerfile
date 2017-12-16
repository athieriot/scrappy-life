FROM php:7.1-cli-alpine

RUN apk add icu icu-libs icu-dev --no-cache \
    && docker-php-ext-install intl
COPY --from=composer:1.5 /usr/bin/composer /usr/bin/composer

COPY . /usr/src/app
WORKDIR /usr/src/app
RUN composer install --no-dev

ENTRYPOINT [ "bin/scrappy" ]
