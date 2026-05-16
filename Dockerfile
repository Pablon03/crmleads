FROM dunglas/frankenphp:1-php8.4

RUN apt-get update && apt-get install -y nodejs npm git unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pdo_pgsql pgsql mbstring xml zip bcmath gd intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN php artisan package:discover --ansi || true
RUN npm run build

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY Caddyfile /etc/caddy/Caddyfile
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
