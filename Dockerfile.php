FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    libicu-dev libpq-dev libzip-dev libpng-dev git unzip \
    && docker-php-ext-install pdo pgsql pdo_pgsql intl zip gd mbstring \
    && apt-get clean

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/openloyalty
COPY . .

RUN if [ -f backend/composer.json ]; then \
        cd backend && composer install --no-scripts --ignore-platform-reqs || true; \
    fi

RUN mkdir -p backend/var && chmod -R 777 backend/var

CMD ["php-fpm"]
