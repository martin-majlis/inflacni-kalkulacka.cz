FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update -y && apt-get upgrade -y \
    && apt-get install -y curl vim unzip git zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

## CONFIGURE
ENV TZ=Europe/Prague