#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Starting Diagpro Laravel Application...${NC}"

# Wait for database to be ready
echo -e "${YELLOW}⏳ Waiting for database connection...${NC}"
until php artisan migrate:status > /dev/null 2>&1; do
    echo -e "${YELLOW}⏳ Database not ready, waiting 5 seconds...${NC}"
    sleep 5
done
echo -e "${GREEN}✅ Database connection established${NC}"

# Wait for Redis to be ready
echo -e "${YELLOW}⏳ Waiting for Redis connection...${NC}"
until php artisan tinker --execute="Redis::ping()" > /dev/null 2>&1; do
    echo -e "${YELLOW}⏳ Redis not ready, waiting 3 seconds...${NC}"
    sleep 3
done
echo -e "${GREEN}✅ Redis connection established${NC}"

# Set proper permissions
echo -e "${YELLOW}🔧 Setting up permissions...${NC}"
chown -R nginx:nginx /var/www
chmod -R 755 /var/www
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

# Create required directories
mkdir -p /var/lib/php82/sessions
chown -R nginx:nginx /var/lib/php82/sessions
chmod 755 /var/lib/php82/sessions

# Create log directories
mkdir -p /var/log/supervisor
mkdir -p /var/log/mysql
touch /var/log/php-errors.log
touch /var/log/php-fpm.log
touch /var/log/php-fpm-slow.log
chown nginx:nginx /var/log/php-*.log

# Laravel setup
echo -e "${YELLOW}🔧 Setting up Laravel...${NC}"

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    echo -e "${YELLOW}🔑 Generating application key...${NC}"
    php artisan key:generate --force
fi

# Clear and cache config
echo -e "${YELLOW}⚡ Optimizing Laravel...${NC}"
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Run migrations
echo -e "${YELLOW}🗄️ Running database migrations...${NC}"
php artisan migrate --force

# Seed database if needed
if [ "$DB_SEED" = "true" ]; then
    echo -e "${YELLOW}🌱 Seeding database...${NC}"
    php artisan db:seed --force
fi

# Create storage link
echo -e "${YELLOW}🔗 Creating storage link...${NC}"
php artisan storage:link

# Clear all caches
echo -e "${YELLOW}🧹 Clearing caches...${NC}"
php artisan cache:clear
php artisan queue:clear

# Start queue worker in background if not in worker container
if [ "$CONTAINER_ROLE" != "worker" ] && [ "$CONTAINER_ROLE" != "scheduler" ]; then
    echo -e "${GREEN}✅ Laravel application ready!${NC}"
    echo -e "${GREEN}🌐 Starting web server...${NC}"
    
    # Start supervisor
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    # This is a worker or scheduler container
    if [ "$CONTAINER_ROLE" = "worker" ]; then
        echo -e "${GREEN}👷 Starting Laravel Queue Worker...${NC}"
        exec php artisan queue:work --verbose --tries=3 --timeout=90 --memory=512
    elif [ "$CONTAINER_ROLE" = "scheduler" ]; then
        echo -e "${GREEN}⏰ Starting Laravel Scheduler...${NC}"
        # Run scheduler every minute
        while true; do
            php artisan schedule:run --verbose --no-interaction &
            sleep 60
        done
    fi
fi