FROM prestashop/prestashop

ENV XDEBUG_CONFIG="remote_enable=on remote_connect_back=on"

RUN pecl install xdebug-2.5.5 \
    && docker-php-ext-enable xdebug
