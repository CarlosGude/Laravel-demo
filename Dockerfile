FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${GID} -S www && adduser -u ${UID} -S www -G www

WORKDIR /var/www/html

COPY --chown=www:www . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

USER www

EXPOSE 9000

CMD ["php-fpm"]
