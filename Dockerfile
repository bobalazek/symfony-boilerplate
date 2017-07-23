# bobalazek / Symfony Boilerplate Docker file

# https://docs.docker.com/samples/php/
FROM php:7.0-apache

### Dependencies

## OS
RUN apt-get update -yq && apt-get upgrade -yq
run apt-get install -yq git \
    curl \
    wget \
    zip \
    unzip \
    apt-utils

## PHP
RUN docker-php-ext-install -j$(nproc) zip mcrypt

# Install composer
RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

## Node
RUN apt-get install -yq nodejs \
    npm

RUN update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

# Install node dependencies
RUN npm install -g bower gulp

### Copy stuff
## App
COPY ./ /var/www/html

## Configuration
# Apache
COPY docker/apache2/sites-available/000-default.conf /etc/apache2/sites-available
COPY docker/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available
COPY docker/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled
COPY docker/apache2/sites-enabled/default-ssl.conf /etc/apache2/sites-enabled

# PHP
COPY docker/php/php.ini /usr/local/etc/php

### Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
