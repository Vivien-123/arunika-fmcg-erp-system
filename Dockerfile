FROM php:8.2-cli

# System dependencies for SQLite + zip
RUN apt-get update && apt-get install -y --no-install-recommends \
    git zip unzip libsqlite3-dev libzip-dev libonig-dev \
    && docker-php-ext-install pdo_sqlite zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . .

# Create .env from template (since .env is gitignored)
RUN cp -n .env.example .env || true

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Generate APP_KEY
RUN php artisan key:generate --force

# Pre-cache routes and views (config cached at runtime for env vars)
RUN php artisan route:cache && php artisan view:cache

# Ensure writable directories
RUN chmod -R 775 storage bootstrap/cache database

EXPOSE 8000

# At startup: cache config (with platform env vars) → seed database → serve
CMD ["sh", "-c", "php artisan config:cache && php artisan db:master-seed && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
