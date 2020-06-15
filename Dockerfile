FROM php:7.2-cli-alpine3.9

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin

RUN mkdir /app
WORKDIR /app

# install dependencies
COPY composer.json composer.lock /app/
RUN composer install --prefer-dist --no-dev --optimize-autoloader

# copy relevant directories
COPY src /app/src

# Add entrypoint script
COPY ./docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]
