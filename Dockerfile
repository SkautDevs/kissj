FROM php:7.1-apache

# Preparation
RUN apt-get update

# Apache2
RUN a2enmod rewrite

# Composer
RUN apt-get -y install curl nano && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy files
COPY . /var/www/html/

# Get dependencies
RUN composer install --no-interaction

