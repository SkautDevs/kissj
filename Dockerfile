FROM php:7.3-apache

# Preparation
RUN apt-get update

# Tools installation
RUN apt-get install -y \
	git \
	zip

# it needs to be here, no clue why
RUN apt-get update

RUN apt-get install -y sqlite3 curl nano

# Apache2
RUN a2enmod rewrite

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Get database
COPY ./db_init.sqlite /var/www/html/db.sqlite
COPY ./sql/init.sql /var/init.sql
RUN sqlite3 db.sqlite3 < /var/init.sql

# Get composer
COPY ./composer.json /var/www/html/
RUN composer install --no-interaction
