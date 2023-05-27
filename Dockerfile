FROM php:8.1-alpine

USER root

# hadolint ignore=DL3022
COPY --from=composer /usr/bin/composer /usr/bin/composer
#COPY config/php/custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini
#COPY config/php/custom-php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom-php.conf

# hadolint ignore=DL3018
RUN apk add \
        --no-cache \
        --repository https://dl-3.alpinelinux.org/alpine/edge/community/ --allow-untrusted \
        --virtual .shadow-deps \
        shadow \
    && usermod -u 1000 www-data \
    && groupmod -g 1000 www-data \
    && apk del .shadow-deps

# Set working directory
WORKDIR /var/www/html/

# Install composer
# Download the Datadog setup extension
#Enable the Datadog extension within app for APM traces
RUN curl -sS https://getcomposer.org/installer \
    && curl -sS  -LO https://github.com/DataDog/dd-trace-php/releases/latest/download/datadog-setup.php \
    && php datadog-setup.php --php-bin=all

USER www-data

# Copy existing application directory contents
COPY --chown=www-data:www-data . /var/www/html

RUN composer require --no-scripts

# Install dependencies
# hadolint ignore=DL3059
RUN composer install

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]
