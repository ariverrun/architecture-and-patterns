FROM php:8.3.3-zts-alpine

RUN apk update \ 
    &&  apk add --no-cache \
    autoconf \
    automake \
    libtool \
    make \
    gcc \
    g++ \
    && pecl install parallel \
    && docker-php-ext-enable parallel

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY ./ /var/www/html

CMD ["php", "-S", "0.0.0.0:8000"]
