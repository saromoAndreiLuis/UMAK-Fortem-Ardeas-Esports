FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install necessary PHP extensions (like pdo_mysql)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Set working directory
WORKDIR /var/www/html

# Copy all app files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd
