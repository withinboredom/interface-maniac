FROM php:8-cli

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apt-get update && apt-get install -y wget git unzip && apt-get clean
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions curl zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json /app/composer.json
COPY composer.lock /app/composer.lock
WORKDIR /app
RUN composer install -o -n --no-dev
COPY . /app
ENTRYPOINT ["php"]
CMD ["src/to-json.php"]
