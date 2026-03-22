# Gunakan PHP 8.2
FROM php:8.2-cli

# Install dependency
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set workdir
WORKDIR /app

# Copy project
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Generate key (optional)
RUN php artisan key:generate || true
RUN chmod +x /app/docker-start.sh

# Expose port
EXPOSE 8000

# Run Laravel
CMD ["/app/docker-start.sh"]
