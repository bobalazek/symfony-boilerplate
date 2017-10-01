########## bobalazek / Symfony Boilerplate Docker file ##########
### General
FROM php:7.0-apache

## Arguments
ARG PROJECT_DIR="/var/www/html"
ARG SYMFONY_ENV="dev"

## Variables
ENV PROJECT_DIR $PROJECT_DIR
ENV SYMFONY_ENV $SYMFONY_ENV

## Config
VOLUME $PROJECT_DIR
WORKDIR $PROJECT_DIR

### Dependencies
## OS
RUN apt-get update -yq && apt-get upgrade -yq
RUN apt-get install -yq apt-utils \
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
    libxml2-dev

## PHP
RUN docker-php-ext-install mysqli \
    dom \
    mbstring \
    zip \
    pdo \
    pdo_mysql \
    gettext

# iconv, mcrypt & gd extensions
RUN apt-get update -yq && apt-get install -yq \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng12-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

## Node
RUN apt-get install -yq nodejs npm

RUN update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10 # Bower fix

# Install node dependencies
RUN npm install -g bower gulp

### Configuration
# Apache
COPY docker/apache2/sites-available/000-default.conf /etc/apache2/sites-available
COPY docker/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available
COPY docker/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled
COPY docker/apache2/sites-enabled/default-ssl.conf /etc/apache2/sites-enabled

# PHP
COPY docker/php/php.ini /usr/local/etc/php

### App
## Copy project into the container
COPY ./ $PROJECT_DIR/

## File permissions
RUN rm -rf $PROJECT_DIR/var/cache/* && \
    rm -rf $PROJECT_DIR/var/logs/*
RUN HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1) && \
    setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX $PROJECT_DIR/var && \
    setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX $PROJECT_DIR/var

### Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
