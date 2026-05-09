FROM dunglas/frankenphp:1.12.2-php8.5

RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip \
    apcu

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
