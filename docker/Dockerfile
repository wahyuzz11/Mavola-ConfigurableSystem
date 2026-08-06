FROM php:8.2-apache

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libonig-dev libxml2-dev libzip-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath zip intl \
    && a2enmod rewrite \
    && git config --system --add safe.directory '*'

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Match container's www-data to your WSL2 user so file ownership just works
RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data

WORKDIR /var/www/html