# Change from php:8.4-fpm to php:8.4-apache
FROM php:8.4-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache rewrite module (important for Laravel)
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first
COPY composer*.json ./

# Install dependencies without scripts (to avoid artisan errors)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy all application files
COPY . .

# Now run the scripts after all files are available
RUN composer run-script post-autoload-dump

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate application key if needed
RUN php artisan key:generate --no-interaction || true

# Cache configuration for production
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Configure Apache for Laravel
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.php\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port 80 instead of 9000
EXPOSE 80

# Start Apache instead of php-fpm
CMD ["apache2-foreground"]