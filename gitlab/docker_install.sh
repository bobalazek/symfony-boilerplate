#!/bin/bash

[[ ! -e /.dockerenv ]] && exit 0

set -xe

### Dependencies

## OS
apt-get update -yqq
apt-get install -yqq nano \
    ssh \
    git \
    curl \
    wget \
    acl \
    zip \
    unzip \
    imagemagick \
    zlib1g-dev \
    apt-utils

## PHP
docker-php-ext-install mysqli \
    gd \
    dom \
    mbstring \
    mcrypt \
    zip \
    pdo \
    pdo_mysql \
    gettext

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

## Node
apt-get install -yqq nodejs npm

ln -fs /usr/bin/nodejs /usr/local/bin/node

# Install node dependencies
npm install -g bower gulp
