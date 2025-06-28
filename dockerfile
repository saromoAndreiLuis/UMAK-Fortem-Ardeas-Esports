FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install necessary libraries and PHP extensions
RUN apt-get update && apt-get install -y \
        libjpeg62-turbo-dev \
        libpng-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install gd pdo pdo_mysql mysqli

# Set working directory
WORKDIR /var/www/html

# Copy all app files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80
