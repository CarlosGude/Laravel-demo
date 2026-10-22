FROM php:8.4.27-fpm-alpine

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
    autoconf \
    dpkg-dev \
    dpkg \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    re2c \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g "${GID}" -S www && adduser -u "${UID}" -S www -G www

WORKDIR /var/www/html

COPY --chown=www:www artisan composer.json composer.lock ./
COPY --chown=www:www app ./app
COPY --chown=www:www bootstrap ./bootstrap
COPY --chown=www:www config ./config
COPY --chown=www:www database ./database
COPY --chown=www:www public ./public
COPY --chown=www:www resources ./resources
COPY --chown=www:www routes ./routes
COPY --chown=www:www storage ./storage

RUN composer install --no-dev --optimize-autoloader --no-interaction

USER www

EXPOSE 9000

CMD ["php-fpm"]
