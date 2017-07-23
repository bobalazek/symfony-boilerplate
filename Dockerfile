# bobalazek / Symfony Boilerplate Docker file

# General
FROM phusion/baseimage

# Use baseimage-docker's init system.
CMD ["/sbin/my_init"]

# General dependencies
RUN apt-get update -yq && apt-get upgrade -yq
run apt-get install -yq git \
    curl \
    wget \
    apt-utils

# Apache
RUN apt-get install -yq apache2 \
    libapache2-mod-php
RUN a2enmod ssl
RUN a2enmod rewrite
RUN service apache2 restart

# PHP
RUN apt-get install -yq php \
    php-cli \
    php-mysql \
    php-mcrypt \
    php-curl \
    php-zip \
    php-gd

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
COPY docker/apache2/sites-available/000-default.conf /etc/apache2/sites-available/
COPY docker/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/
COPY docker/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/
COPY docker/apache2/sites-enabled/default-ssl.conf /etc/apache2/sites-enabled/

# Expose ports
EXPOSE 80
EXPOSE 443

# Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
