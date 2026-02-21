# ЭТАП 1: Сборка фронтенда (Node.js)
FROM node:24-alpine AS frontend

WORKDIR /app

# Копируем только файлы зависимостей для кэширования
COPY ./app/package*.json ./
RUN npm ci

# Копируем исходники и собираем проект
COPY ./app .
RUN npm run build

# ЭТАП 2: PHP
FROM php:8.5-fpm

# Установка зависимостей и расширений
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    netcat-openbsd \
    libicu-dev

# Очистка кэша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем конифиги
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Создание пользователя
RUN useradd -G www-data,root -u 1000 -d /home/appuser appuser
RUN mkdir -p /home/appuser/.composer && \
    chown -R appuser:appuser /home/appuser

WORKDIR /var/www/html/app

COPY --chown=appuser:appuser ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Копируем собранные ассеты из этапа frontend
COPY --from=frontend --chown=appuser:appuser /app/public/build ./public/build

USER appuser

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]