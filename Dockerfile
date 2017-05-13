# bobalazek / Symfony Boilerplate Docker file

# General
FROM phusion/baseimage

## Use baseimage-docker's init system.
CMD ["/sbin/my_init"]

# Install dependencies
RUN apt-get update
RUN apt-get install -yq git curl zip unzip wget curl supervisor
RUN apt-get install -yq apt-utils

## Server
### Apache
RUN apt-get install -yq apache2
RUN apt-get install -yq libapache2-mod-php
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod ssl
RUN a2enmod rewrite
RUN service apache2 restart

### PHP
RUN apt-get install -yq php php-cli php-mysql php-mcrypt php-curl php-zip php-gd

#### Composer
RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer


## Node
RUN apt-get install -yq nodejs npm

### NPM
RUN npm install -g bower gulp


#### Fix node path
RUN update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10


# Cleanup
RUN apt-get clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# Set workdir
WORKDIR /


# Expose ports
EXPOSE 80 3306
