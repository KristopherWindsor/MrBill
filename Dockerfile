FROM php:7.1-apache

RUN apt-get update && apt-get install -y
RUN apt-get install -y git zip unzip

RUN a2enmod rewrite

RUN pecl install redis-3.1.1 \
    && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/

RUN mkdir /var/www/data && chmod 0755 /var/www/data

RUN cd /var/www && composer install

RUN chown -R www-data:www-data /var/www/*
