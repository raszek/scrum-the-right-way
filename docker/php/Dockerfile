FROM php:8.4-apache

RUN apt-get update
RUN apt-get install -y \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    wget \
    libzip-dev \
    unzip \
    vim

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install symfony cli
RUN curl -sS https://get.symfony.com/cli/installer | bash

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN docker-php-ext-install gd zip bcmath intl

COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod proxy_http

WORKDIR /var/www/html/scrum-right-way
EXPOSE 8000

ADD start.sh /start.sh
RUN chmod +x /start.sh

ENTRYPOINT ["bash", "/start.sh"]
