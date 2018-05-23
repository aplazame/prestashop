FROM prestashop/prestashop

ARG XDEBUG_REMOTE_OPTION=xdebug.remote_connect_back=on

RUN pecl install xdebug-2.5.5 \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo $XDEBUG_REMOTE_OPTION >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.default_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.coverage_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini
