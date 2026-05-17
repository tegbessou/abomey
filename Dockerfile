FROM dunglas/frankenphp:1.12.2-php8.5

RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip \
    apcu

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        chromium chromium-driver \
        fonts-liberation \
 && rm -rf /var/lib/apt/lists/*

ENV PANTHER_CHROME_BINARY=/usr/bin/chromium
ENV PANTHER_CHROME_DRIVER_BINARY=/usr/bin/chromedriver
ENV PANTHER_NO_SANDBOX=1

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
