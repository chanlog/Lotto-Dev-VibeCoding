FROM php:8.2-fpm

# 시스템 의존성 설치
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 작업 디렉토리 설정
WORKDIR /var/www

# 소스 코드 복사
COPY . .

# 필요한 디렉토리 생성 및 권한 설정
RUN mkdir -p bootstrap/cache storage/logs storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views \
    && chmod -R 755 storage bootstrap/cache

# 의존성 설치
RUN composer install --no-scripts --no-dev --optimize-autoloader

# 소유권 변경
RUN chown -R www-data:www-data /var/www

# 포트 노출
EXPOSE 8000

# 개발 서버 시작
CMD php artisan serve --host=0.0.0.0 --port=8000