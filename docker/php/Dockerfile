FROM php:8.3-fpm-alpine

LABEL maintainer="LinkForge Team"
LABEL org.opencontainers.image.source="https://github.com/goshgarhasanov/linkforge"

RUN apk add --no-cache \
        bash \
        git \
        curl \
        zip \
        unzip \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        autoconf \
        gcc \
        g++ \
        make \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        intl \
        gd \
        bcmath \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf gcc g++ make linux-headers \
    && rm -rf /var/cache/apk/* /tmp/*

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-linkforge.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

WORKDIR /var/www/html

RUN addgroup -g 1000 linkforge \
    && adduser -u 1000 -G linkforge -s /bin/bash -D linkforge \
    && chown -R linkforge:linkforge /var/www/html

USER linkforge

EXPOSE 9000

CMD ["php-fpm"]
