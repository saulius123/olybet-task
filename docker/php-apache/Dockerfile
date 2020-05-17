FROM php:7.3-apache
COPY ./conf/php.ini /usr/local/etc/php/
RUN apt-get update && \
    apt-get install -y vim locales \
## git needed for composer
    git \
## Mysql client needed for drush DB commands
    mariadb-client \
    libzip-dev \
    libcurl4 libcurl3-dev \
    zlib1g-dev libjpeg62-turbo-dev libpng-dev libfreetype6-dev && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
## Configure locales and timezones
    echo "Europe/Vilnius" > /etc/timezone && \
    dpkg-reconfigure -f noninteractive tzdata && \
    sed -i -e 's/# lt_LT.UTF-8 UTF-8/lt_LT.UTF-8 UTF-8/' /etc/locale.gen && \
    echo 'LANG="lt_LT.UTF-8"'>/etc/default/locale && \
    dpkg-reconfigure --frontend=noninteractive locales && \
    update-locale LANG=lt_LT.UTF-8

RUN apt-get update

RUN pecl install apcu
RUN docker-php-ext-enable apcu
RUN docker-php-ext-install mysqli

ADD ./conf/000-default.conf /etc/apache2/sites-available/

# Apache configuration
RUN apt-get update
RUN apt-get install -y gnupg
RUN apt-get install -y mc
RUN apt-get install -y wget
RUN docker-php-ext-install pdo_mysql curl gd zip

RUN a2enmod rewrite

RUN apt-get install aptitude -y \
    && git clone https://github.com/phpredis/phpredis.git \
    && mv phpredis/ /etc/ \
    && cd /etc/phpredis  \
    && phpize  \
    && ./configure  \
    && make  \
    && make install
RUN touch /usr/local/etc/php/conf.d/redis.ini \
    && echo 'extension=redis.so' > /usr/local/etc/php/conf.d/redis.ini
RUN apt-get install redis-tools -y

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
ENV PATH="/var/www/vendor/bin:${PATH}"

# Midnight Commander
ENV TERM=xterm
RUN apt-get install -y mc

RUN rm -rf /var/www/html