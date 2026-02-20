#!/bin/bash
set -e

cd /var/www/html/app

# Цвета для логов
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🚀 Starting application...${NC}"

echo -e "${YELLOW}⏳ Waiting for database connection...${NC}"
while ! nc -z db 3306; do
  sleep 1
done
echo -e "${GREEN}✅ Database is ready!${NC}"

echo -e "${YELLOW}📦 Running public filament assets...${NC}"
php artisan filament:assets
echo -e "${GREEN}✅ Public filament assets completed!${NC}"

echo -e "${YELLOW}📦 Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed!${NC}"

echo -e "${YELLOW}⚙️ Optimizing configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✅ Configuration optimized!${NC}"

echo -e "${GREEN}🎉 Starting PHP-FPM...${NC}"
exec php-fpm