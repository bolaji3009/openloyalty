FROM php:8.1-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Configurar pgsql
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/include/postgresql/

# Instalar extensiones PHP una por una
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip
RUN docker-php-ext-install gd
RUN docker-php-ext-install mbstring

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/openloyalty

# Copiar código
COPY . .

# Instalar dependencias PHP (permisivo)
RUN if [ -f backend/composer.json ]; then \
        cd backend && \
        composer install --no-scripts --ignore-platform-reqs --no-interaction || true; \
    fi

# Permisos
RUN mkdir -p backend/var backend/app/cache backend/app/logs \
    && chmod -R 777 backend/var backend/app/cache backend/app/logs 2>/dev/null || true

EXPOSE 9000

CMD ["php-fpm"]
