FROM php:8.2-apache

# Install dependencies yang dibutuhkan CI4 & PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Ubah Document Root Apache ke folder public CodeIgniter 4
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf

# Salin seluruh file project ke dalam container
COPY . /var/www/html

# Berikan izin akses folder writable
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/writable

EXPOSE 80