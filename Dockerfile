# bobalazek / Symfony Boilerplate Docker file

# https://docs.docker.com/samples/php/
FROM php:7.0-apache

# General dependencies
RUN apt-get update -yq && apt-get upgrade -yq
run apt-get install -yq git \
    curl \
    wget \
    apt-utils

# Install composer
RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# Node
RUN apt-get install -yq nodejs \
    npm

RUN update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

RUN npm install -g bower gulp

# Copy app to the container
COPY ./ /var/www/html/

# Copy apache stuff to the container
COPY docker/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
COPY docker/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/default-ssl.conf
COPY docker/apache2/sites-enabled/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.conf

# Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
