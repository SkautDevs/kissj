FROM php:8.0-apache

# Preparation for apt
RUN set -ex
RUN apt-get update

# Tools installation
RUN apt-get install -y \
	sqlite3 \
	curl \
	nano \
	git \
	zip

# Preparation for postgress
RUN apt-get install -y \
    libpq-dev

RUN docker-php-ext-install \
  pcntl \ 
  pdo \ 
  pdo_pgsql \
  pgsql
  
#RUN yes | pecl install xdebug-3.0.4 \
    #&& echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    #&& echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    #&& echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

# Refresh apache2
RUN a2enmod rewrite

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
