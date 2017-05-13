# bobalazek / Symfony Boilerplate Docker file

# General
FROM phusion/baseimage

# General dependencies
RUN apt-get update
RUN apt-get install -yq git curl zip wget curl

# PHP dependencies
RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# Node dependencies
RUN npm install -g bower gulp

# Copy app to the container & set the workdir
COPY ./ /var/www/html
WORKDIR /var/www/html

# Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Use baseimage-docker's init system.
CMD ["/sbin/my_init"]
