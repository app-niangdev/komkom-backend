FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache nginx supervisor postgresql-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath

WORKDIR /var/www
COPY --from=vendor /app /var/www

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN addgroup -g 1000 laravel && adduser -G laravel -u 1000 -D laravel \
    && chown -R laravel:laravel /var/www \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
