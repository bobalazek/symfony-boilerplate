########## bobalazek / Symfony Boilerplate Docker file ##########
FROM php:7.0-apache

### Variables
ENV PROJECT_DIR /var/www/html
ENV SYMFONY_ENV dev

## Config
VOLUME $PROJECT_DIR
WORKDIR $PROJECT_DIR

### Dependencies

## OS
RUN apt-get update -yq && apt-get upgrade -yq
run apt-get install -yq apt-utils \
    nano \
    ssh \
    git \
    curl \
    wget \
    acl \
    zip \
    unzip \
    imagemagick \
    zlib1g-dev \

## PHP
RUN docker-php-ext-install mysqli \
    gd \
    imagick \
    dom \
    mbstring \
    mcrypt \
    cli \
    zip \
    pdo \
    pdo_mysql \
    gettext

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
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
