#!/bin/bash

# MPWA WhatsApp Gateway - Production Deployment Script
# Server: wablast.inilaku.com
# Mode: HTTP Polling (Shared Hosting Compatible)

echo "======================================"
echo "MPWA Production Deployment"
echo "======================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running in correct directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Please run this script from Laravel root directory.${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Setup Environment${NC}"
if [ -f ".env" ]; then
    echo -e "${YELLOW}.env already exists. Backup? (y/n)${NC}"
    read -r backup
    if [ "$backup" = "y" ]; then
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        echo -e "${GREEN}Backup created${NC}"
    fi
fi

echo -e "${YELLOW}Copy .env.prod to .env? (y/n)${NC}"
read -r copy_env
if [ "$copy_env" = "y" ]; then
    cp .env.prod .env
    echo -e "${GREEN}.env created from .env.prod${NC}"
else
    echo -e "${YELLOW}Skipped${NC}"
fi

echo ""
echo -e "${YELLOW}Step 2: Install Dependencies${NC}"
echo "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

echo "Installing NPM dependencies..."
npm install --production

echo ""
echo -e "${YELLOW}Step 3: Generate Application Key${NC}"
php artisan key:generate

echo ""
echo -e "${YELLOW}Step 4: Run Migrations${NC}"
echo -e "${YELLOW}Run migrations? (y/n)${NC}"
read -r run_migrate
if [ "$run_migrate" = "y" ]; then
    php artisan migrate --force
    echo -e "${GREEN}Migrations completed${NC}"
else
    echo -e "${YELLOW}Skipped - You can run manually: php artisan migrate${NC}"
fi

echo ""
echo -e "${YELLOW}Step 5: Storage Link${NC}"
php artisan storage:link

echo ""
echo -e "${YELLOW}Step 6: Set Permissions${NC}"
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 credentials
chmod 644 .env
echo -e "${GREEN}Permissions set${NC}"

echo ""
echo -e "${YELLOW}Step 7: Cache Configuration${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}Caches created${NC}"

echo ""
echo -e "${YELLOW}Step 8: Node.js Server${NC}"
echo "Starting Node.js server with PM2..."
if command -v pm2 &> /dev/null; then
    pm2 delete mpwa-whatsapp 2>/dev/null
    pm2 start server.js --name "mpwa-whatsapp"
    pm2 save
    echo -e "${GREEN}Node.js server started${NC}"
else
    echo -e "${RED}PM2 not found. Please install: npm install -g pm2${NC}"
    echo -e "${YELLOW}Or start manually: node server.js${NC}"
fi

echo ""
echo "======================================"
echo -e "${GREEN}Deployment Complete!${NC}"
echo "======================================"
echo ""
echo "Next Steps:"
echo "1. Configure cron jobs (see DEPLOYMENT_GUIDE.md)"
echo "2. Test website: https://wablast.inilaku.com"
echo "3. Test Node.js: https://wablast.inilaku.com:3100"
echo "4. Add WhatsApp device and test QR code scanning"
echo ""
echo "Logs:"
echo "- Laravel: storage/logs/laravel.log"
echo "- Node.js: pm2 logs mpwa-whatsapp"
echo ""
echo "Management Commands:"
echo "- Check status: pm2 status"
echo "- View logs: pm2 logs mpwa-whatsapp"
echo "- Restart: pm2 restart mpwa-whatsapp"
echo ""
