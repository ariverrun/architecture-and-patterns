FROM phpswoole/swoole:latest

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY ./ /var/www/html

CMD ["php", "-S", "0.0.0.0:8000"]
