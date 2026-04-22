FROM php:8.2-apache

# نسخ المشروع
COPY . /var/www/html/

# تثبيت extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# تفعيل mod_rewrite لو محتاجه
RUN a2enmod rewrite

EXPOSE 80