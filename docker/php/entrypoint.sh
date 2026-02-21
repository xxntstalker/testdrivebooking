#!/bin/bash
set -e

cd /var/www/html/app

# Цвета для логов
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}🚀 Starting application...${NC}"

# 1. Ждём подключения к базе данных
echo -e "${YELLOW}⏳ Waiting for database connection...${NC}"
while ! nc -z db 3306; do
  sleep 1
done
echo -e "${GREEN}✅ Database is ready!${NC}"

# 2. Установка Composer зависимостей (если нет vendor)
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✅ Composer dependencies installed!${NC}"
fi

# 3. Генерация ключа приложения (если нет)
if [ ! -f ".env" ] || [ -z "$(grep APP_KEY .env)" ]; then
    echo -e "${YELLOW}🔑 Generating application key...${NC}"
    php artisan key:generate
    echo -e "${GREEN}✅ Application key generated!${NC}"
fi

# 4. Генерация ассетов для филамента
echo -e "${YELLOW}📦 Running public filament assets...${NC}"
php artisan filament:assets
echo -e "${GREEN}✅ Public filament assets completed!${NC}"

# 5. Запуск миграций
echo -e "${YELLOW}📦 Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed!${NC}"

# 6. Посев демо-данных
echo -e "${YELLOW}🌱 Seeding demo data...${NC}"
php artisan db:seed
echo -e "${GREEN}✅ Demo data seeded!${NC}"

# 7. Для продакшена нужно включить Кэширование, для разработки - очищаем кеш
echo -e "${YELLOW}🔧 Development mode - clearing cache${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✅ Clearing cache!${NC}"

# Вывод учётных данных
echo -e "${GREEN}"
echo -e "============================================"
echo -e "  🎉 Приложение готово!"
echo -e "============================================"
echo -e "  📍 Customer: http://localhost:8085"
echo -e "  📍 Admin:    http://localhost:8085/admin"
echo -e "  👤 Email:    manager@example.com"
echo -e "  🔑 Password: password"
echo -e "  📚 Swagger:  http://localhost:8086"
echo -e "============================================"
echo -e "${NC}"

echo -e "${GREEN}🎉 Starting PHP-FPM...${NC}"
exec php-fpm