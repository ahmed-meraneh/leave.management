FROM php:8.2-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_ENABLE_CLI=0
ENV PHP_OPCACHE_VALIDATE_TIMESTAMP=0
ENV PHP_OPCACHE_REVALIDATE_FREQ=0

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions zip intl opcache pdo pdo_mysql mbstring bcmath

COPY ./tools/docker/php.ini /usr/local/etc/php/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

WORKDIR /var/www/leave.management

COPY --chown=www-data:www-data . .

RUN composer install --optimize-autoloader --no-dev

EXPOSE 9000

CMD ["php-fpm"]
