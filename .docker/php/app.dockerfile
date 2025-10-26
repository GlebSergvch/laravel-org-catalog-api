FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    unzip \
    librdkafka-dev \
    wget \
    libzip-dev \
    libxml2-dev \
    --no-install-recommends \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql -j$(nproc) gd curl mbstring exif zip xml

RUN pecl install rdkafka
COPY ./php.ini /usr/local/etc/php/

RUN pecl install -f xdebug-3.2.0 \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable xdebug

RUN apt-get update && apt-get install -y curl && \
  curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN mkdir -p /usr/local/share/ca-certificates/Yandex && \
    wget "https://storage.yandexcloud.net/cloud-certs/CA.pem" \
         --output-document /usr/local/share/ca-certificates/Yandex/YandexInternalRootCA.crt && \
    chmod 0655 /usr/local/share/ca-certificates/Yandex/YandexInternalRootCA.crt


EXPOSE 9000
CMD ["php-fpm"]
