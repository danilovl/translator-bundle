FROM php:8.5-alpine

RUN apk update \
  && apk add gmp-dev \
  && docker-php-ext-install gmp

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html/app

CMD tail -f /dev/null
