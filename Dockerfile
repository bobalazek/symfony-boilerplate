# bobalazek / Symfony Boilerplate Docker file

# General
FROM phusion/baseimage

# General dependencies
RUN apt-get update
RUN apt-get install -yq git curl zip unzip wget curl
RUN apt-get install -yq apt-utils

# Apache
RUN apt-get install -yq apache2
RUN apt-get install -yq libapache2-mod-php
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod ssl
RUN a2enmod rewrite
RUN service apache2 restart

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# PHP
RUN apt-get install -yq php php-cli php-mysql php-mcrypt php-curl php-zip php-gd

RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# Node
RUN apt-get install -yq nodejs npm

RUN update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

RUN npm install -g bower gulp

# Copy app to the container & set the workdir
COPY ./ /var/www/html
WORKDIR /var/www/html

# Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Expose ports
EXPOSE 80
EXPOSE 443
EXPOSE 3306

# Use baseimage-docker's init system.
CMD ["/sbin/my_init"]
