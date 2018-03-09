FROM php:7.2-fpm-alpine

RUN apk add --no-cache git openssl \
    && apk add --no-cache --virtual .persistent-deps postgresql-dev icu-dev libmcrypt-dev \
    && apk add --no-cache --virtual .build-deps build-base gmp-dev zlib-dev \
    && docker-php-ext-install mbstring opcache pdo pdo_mysql zip \
    # Cleanup
    && apk del .build-deps \
    && rm -rf /tmp/*

# Install PHP Extensions
RUN apk add --no-cache --virtual .build-deps build-base autoconf git \
    && yes | pecl install apcu opcache \
    # APCU config
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini \
    && echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/apcu.ini \
    # Cleanup
    && apk del .build-deps \
    && rm -rf /tmp/*

# Add Composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl --silent --show-error https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer && \
    composer global require hirak/prestissimo && \
    composer clear-cache

ADD . /var/www/html
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html
ENV APP_ENV prod

# Blackfire
#RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
#    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/amd64/$version \
#    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
#    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
#    && echo "extension=blackfire.so" > /usr/local/etc/php/conf.d/blackfire.ini \
#    && echo "blackfire.agent_socket=tcp://blackfire:8707" >> /usr/local/etc/php/conf.d/blackfire.ini

RUN composer install --no-dev --no-scripts --no-autoloader --no-suggest && \
    composer clear-cache

RUN composer dump --optimize
#RUN bin/console --env=prod cache:warmup
#RUN bin/phpunit tests

CMD ["php-fpm", "--allow-to-run-as-root"]
