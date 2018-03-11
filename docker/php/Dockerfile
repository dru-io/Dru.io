FROM php:7.2-fpm-alpine

ARG DRUPAL_ENV=prod

RUN apk add --no-cache --virtual .persistent-deps git curl openssl freetype libpng libjpeg-turbo \
    && apk add --no-cache --virtual .build-deps build-base libpng-dev freetype-dev libjpeg-turbo-dev tzdata zlib-dev \
    && docker-php-ext-install mbstring opcache pdo_mysql zip bcmath exif \
    # GD
    && docker-php-ext-configure gd \
        --with-gd \
        --with-freetype-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
    && docker-php-ext-install -j${NPROC} gd \
    # Set Europe/Moscow as local timezone
    && cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime \
    && echo "Europe/Moscow" > /etc/timezone \
    # Cleanup
    && apk del .build-deps \
    && rm -rf /tmp/*

# Install PHP Extensions
RUN apk add --no-cache --virtual .build-deps build-base autoconf \
    && yes | pecl install apcu xdebug opcache \
    # APCU
    && echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini \
    && echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/apcu.ini \
    # Opcache
    && echo "zend_extension=opcache.so" > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.enable = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.validate_timestamps = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq = 2" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files = 20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption = 96" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer = 16" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.fast_shutdown = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    # Cleanup
    && apk del .build-deps \
    && rm -rf /tmp/*

RUN if [ $DRUPAL_ENV = "dev" ] ; then \
    # Xdebug config
    echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable = 1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart = 1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_host = 127.0.0.1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.max_nesting_level = 1000" >> /usr/local/etc/php/conf.d/xdebug.ini \
    ; fi

# Add Composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl --silent --show-error https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer && \
    composer global require hirak/prestissimo && \
    composer clear-cache

ADD . /var/www/html
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

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
