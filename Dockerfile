FROM richarvey/nginx-php-fpm:3.1.6

WORKDIR /var/www/html
COPY . /var/www/html

# Node for Vite build (so CSS works everywhere)
RUN apk add --no-cache nodejs npm

# PHP deps
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Frontend build (IMPORTANT)
RUN npm ci && npm run build

# Permissions
RUN chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Nginx config
COPY conf/nginx/site.conf /etc/nginx/sites-enabled/default.conf