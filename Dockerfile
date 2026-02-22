FROM php:8.5-cli-trixie AS upstream

FROM upstream as base
WORKDIR /app

RUN apt update && apt install -y --no-install-recommends \
    unzip \
    && apt -y autoremove \
    && apt clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath uuid

FROM base AS dev

ENV COMPOSER_ALLOW_SUPERUSER=1
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN groupadd -g ${GROUP_ID} devuser && \
    useradd -l -u ${USER_ID} -g devuser -m devuser

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN install-php-extensions xdebug

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/dev/xdebug.ini "$PHP_INI_DIR/conf.d/xdebug.ini"

RUN chown devuser:devuser /app
USER devuser

FROM base as builder
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.json

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

FROM base as prod

COPY . .
COPY --from=builder /app/vendor ./vendor

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/prod/opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"

RUN rm /usr/local/bin/install-php-extensions /app/composer.json

CMD ["php", "./public/test.php"]