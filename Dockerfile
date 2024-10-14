FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update

RUN apt-get install -y \
    libonig-dev \
    libpng-dev \
    libzip-dev \
    zlib1g-dev

RUN docker-php-ext-install gd mbstring pdo pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install \
    --no-ansi \
    --no-interaction \
    --no-scripts \
    --no-progress \
    --prefer-dist

RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD php artisan optimize && php-fpm