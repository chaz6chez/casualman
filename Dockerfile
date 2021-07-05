FROM php:7.3.28-alpine3.13
LABEL maintainer="250220719@qq.com" version="1.0.0"
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories && \
    apk update && \
    apk add --no-cache \
    autoconf \
    build-base \
    libevent-dev \
    rabbitmq-c-dev \
    openssl-dev && \
    docker-php-ext-install sockets pcntl pdo_mysql bcmath && \
    pecl install redis && \
    pecl install amqp && \
    docker-php-ext-enable redis amqp opcache && \
    pecl install event
COPY ./event.ini /usr/local/etc/php/conf.d/

RUN apk add --no-cache \
    libuuid \
    e2fsprogs-dev && \
    pecl install uuid && \
    docker-php-ext-enable uuid
    
EXPOSE 5454 6464
CMD ["top"]
VOLUME /var/www
WORKDIR /var/www
