# ==============================================================================
# 1. BASE STAGE
# Sets up PHP, system dependencies, and extensions used everywhere.
# ==============================================================================
FROM php:8.2-fpm-alpine AS base

# Install system dependencies required by Symfony
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    unzip \
    git \
    linux-headers \
    postgresql-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_pgsql zip opcache

WORKDIR /var/www/html

# ==============================================================================
# 2. VENDOR/BUILDER STAGE
# Downloads production dependencies in a separate layer.
# ==============================================================================
FROM base AS vendor

# Bring in Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy only the files needed to resolve dependencies
COPY composer.json composer.lock symfony.lock ./

# Install ONLY production dependencies, highly optimized
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ==============================================================================
# 3. DEVELOPMENT STAGE
# Used for local development. Includes Xdebug, Composer, and testing tools.
# ==============================================================================
FROM base AS dev

# Use development PHP config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install Xdebug
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
        && pecl channel-update pecl.php.net \
        && pecl install xdebug \
        && docker-php-ext-enable xdebug

# Bring in Composer (needed for installing dev dependencies locally)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Note: We do NOT copy the application code here.
# In development, you will mount your local directory as a volume so changes sync instantly.

# ==============================================================================
# 4. PRODUCTION STAGE
# The final image. Highly optimized, no dev tools, ready to deploy.
# ==============================================================================
FROM base AS prod

# Use production PHP config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy the built vendor directory from the builder stage
COPY --from=vendor /var/www/html/vendor ./vendor

# Copy the rest of the application code
COPY . .

# Set permissions for the web server user
RUN chown -R www-data:www-data /var/www/html var/

# Warm up the Symfony cache for production
# (Run as www-data to ensure cache files have the correct owner)
USER www-data
RUN APP_ENV=prod php bin/console cache:clear \
    && APP_ENV=prod php bin/console cache:warmup

# Expose port 9000 for PHP-FPM
EXPOSE 9000
