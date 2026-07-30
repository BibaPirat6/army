# ---------------- DEPENDENCIES (PHP vendor) ----------------
FROM php:8.3-cli AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev libicu-dev libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo pdo_mysql zip intl bcmath gd


COPY composer.json composer.lock ./

RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-dev --prefer-dist --no-interaction \
    --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev

# потом уже весь код
COPY . .


# ---------------- FRONTEND ----------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build


# ---------------- RUNTIME ----------------
FROM php:8.3-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    bash unzip \
    icu-dev libzip-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev

RUN docker-php-ext-install \
    pdo pdo_mysql zip intl bcmath gd

# копируем только нужное
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app ./
COPY --from=frontend /app/public/build ./public/build

# минимально нужные папки Laravel
RUN mkdir -p storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]