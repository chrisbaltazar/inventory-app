FROM php:8.1.34-trixie

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Create a non-root user
RUN useradd -m -u 1000 -s /bin/bash app

WORKDIR /var/www/html

# Change ownership of the working directory
RUN chown -R app:app /var/www/html

# Switch to non-root user
USER app

EXPOSE 8000
