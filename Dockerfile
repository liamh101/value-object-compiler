ARG PHP_VERSION

FROM --platform=linux/amd64 php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip

ARG PHP_XDEBUG_VERSION

RUN pecl install xdebug-${PHP_XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug

RUN docker-php-ext-install zip

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup --gid $GROUP_ID user
RUN adduser --disabled-password --gecos '' --uid $USER_ID --gid $GROUP_ID user
RUN usermod -aG www-data user

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

USER user
