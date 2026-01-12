FROM php:8.1-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    locales \
    libicu-dev \
    zlib1g-dev \
    libpq-dev \
    git \
    libcurl4-openssl-dev \
    vim \
    netcat-openbsd \
    postgresql-client \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    && locale-gen C.UTF-8 \
    && update-locale LANG=C.UTF-8 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/include/postgresql/ \
    && docker-php-ext-configure zip \
    && docker-php-ext-install \
    pdo pgsql pdo_pgsql intl opcache bcmath zip curl gd mbstring

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Configurar usuario www-data
RUN usermod --non-unique --uid 1000 www-data \
    && usermod -s /bin/bash www-data

WORKDIR /var/www/openloyalty

# Copiar aplicación
COPY . .

# Instalar dependencias
RUN if [ -f backend/composer.json ]; then \
    cd backend && composer install --no-dev --optimize-autoloader; \
    fi

# Permisos
RUN chown -R www-data:www-data /var/www/openloyalty \
    && chmod -R 755 /var/www/openloyalty \
    && mkdir -p backend/var/cache backend/var/log \
    && chmod -R 777 backend/var

EXPOSE 9000

CMD ["php-fpm"]