FROM php:8.5-cli-trixie AS base

WORKDIR /app

FROM base AS dev

ENV COMPOSER_ALLOW_SUPERUSER=1
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN groupadd -g ${GROUP_ID} devuser && \
    useradd -l -u ${USER_ID} -g devuser -m devuser

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apt update && apt install -y --no-install-recommends \
    unzip \
    && apt -y autoremove \
    && apt clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions bcmath xdebug

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/xdebug.ini "$PHP_INI_DIR/conf.d/xdebug.ini"

RUN chown devuser:devuser /app
USER devuser