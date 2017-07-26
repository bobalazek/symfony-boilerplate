# bobalazek / Symfony Boilerplate Docker file

# https://docs.docker.com/samples/php/
FROM php:7.0-apache

### Environment
ENV PROJECT_DIR /var/www/html
ENV SYMFONY_ENV dev

VOLUME $PROJECT_DIR
WORKDIR $PROJECT_DIR

### Dependencies

## OS
RUN apt-get update -yq && apt-get upgrade -yq
run apt-get install -yq git \
    curl \
    wget \
    zip \
    unzip \
    zlib1g-dev \
    apt-utils

## PHP
RUN docker-php-ext-install mysqli \
    zip \
    pdo \
    pdo_mysql \
    gettext

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');"

## Node
RUN apt-get install -yq nodejs \
    npm

RUN update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

# Install node dependencies
RUN npm install -g bower gulp

### Copy stuff
## App
COPY ./ $PROJECT_DIR/

## Configuration
## Apache
COPY docker/apache2/sites-available/000-default.conf /etc/apache2/sites-available
COPY docker/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available
COPY docker/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled
COPY docker/apache2/sites-enabled/default-ssl.conf /etc/apache2/sites-enabled

## PHP
COPY docker/php/php.ini /usr/local/etc/php

## App
# File permissions
RUN rm -rf $PROJECT_DIR/var/cache/* && \
    rm -rf $PROJECT_DIR/var/logs/*
RUN HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1) && \
    setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX $PROJECT_DIR/var && \
    setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX $PROJECT_DIR/var

### Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
