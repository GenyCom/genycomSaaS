FROM php:8.4-cli

# Télécharger le script d'installation des extensions PHP
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Installer les extensions (le script gère apt-get et les dépendances automatiquement)
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    bcmath \
    exif \
    pcntl \
    gd \
    zip \
    intl \
    opcache

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]