FROM php:7.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libmcrypt-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip \
    wget \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure extensions
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/include/postgresql/

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pgsql \
    pdo_pgsql \
    intl \
    zip \
    gd \
    mcrypt \
    bcmath

# Install Composer
COPY --from=composer:1.10 /usr/bin/composer /usr/bin/composer

# Install Phing
RUN wget https://www.phing.info/get/phing-latest.phar -O /usr/local/bin/phing && chmod +x /usr/local/bin/phing

WORKDIR /var/www/openloyalty

# Copy code
COPY . .

# Permissions
RUN mkdir -p backend/var backend/app/cache backend/app/logs \
    && chmod -R 777 backend/var backend/app/cache backend/app/logs 2>/dev/null || true

EXPOSE 9000

CMD ["php-fpm"]
