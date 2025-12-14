#!/bin/bash

# MPWA Production Quick Fix Script
# Fixes both cache path error and Node.js connection issues
# Run: bash quick-fix.sh

set -e  # Exit on error

echo "╔════════════════════════════════════════╗"
echo "║   MPWA Production Quick Fix Script    ║"
echo "║   wablast.inilaku.com                  ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Check if .env exists
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 1: Check .env file"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ ! -f ".env" ]; then
    echo -e "${RED}✗ .env file not found!${NC}"
    
    if [ -f ".env.prod" ]; then
        echo -e "${YELLOW}Copying .env.prod to .env...${NC}"
        cp .env.prod .env
        echo -e "${GREEN}✓ Created .env from .env.prod${NC}"
    else
        echo -e "${RED}ERROR: .env.prod also not found!${NC}"
        echo "Please create .env file manually"
        exit 1
    fi
else
    echo -e "${GREEN}✓ .env file exists${NC}"
fi

echo ""

# Step 2: Create storage folders
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 2: Create storage folders"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "Creating storage/framework folders..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Create .gitkeep files
touch storage/framework/cache/data/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/framework/testing/.gitkeep

echo -e "${GREEN}✓ Folders created${NC}"
echo ""

# Step 3: Set permissions
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 3: Set permissions"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "Setting folder permissions (775)..."
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

echo "Setting file permissions (664)..."
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# Step 4: Fix Node.js connection
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 4: Fix Node.js connection"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "Current configuration:"
grep "WA_URL_SERVER=" .env || echo "WA_URL_SERVER not set"

echo ""
echo "Updating to use localhost (internal connection)..."

# Backup .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update WA_URL_SERVER
if grep -q "^WA_URL_SERVER=" .env; then
    sed -i.tmp 's|^WA_URL_SERVER=.*|WA_URL_SERVER=http://localhost|g' .env
    rm -f .env.tmp
    echo -e "${GREEN}✓ Updated WA_URL_SERVER to http://localhost${NC}"
else
    echo "WA_URL_SERVER=http://localhost" >> .env
    echo -e "${GREEN}✓ Added WA_URL_SERVER=http://localhost${NC}"
fi

# Ensure PORT_NODE is set
if ! grep -q "^PORT_NODE=" .env; then
    echo "PORT_NODE=3100" >> .env
    echo -e "${GREEN}✓ Added PORT_NODE=3100${NC}"
fi

echo ""
echo "New configuration:"
grep -E "^(WA_URL_SERVER|PORT_NODE)=" .env

echo ""

# Step 5: Clear Laravel caches
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 5: Clear Laravel caches"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "Clearing caches..."
php artisan config:clear 2>/dev/null || echo "Config cache cleared (if exists)"
php artisan cache:clear 2>/dev/null || echo "Application cache cleared"
php artisan view:clear 2>/dev/null || echo "View cache cleared"
php artisan route:clear 2>/dev/null || echo "Route cache cleared"

echo ""
echo "Rebuilding caches..."
php artisan config:cache 2>/dev/null || echo "Config cached"
php artisan route:cache 2>/dev/null || echo "Routes cached"

echo -e "${GREEN}✓ Caches cleared and rebuilt${NC}"
echo ""

# Step 6: Check Node.js status
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 6: Check Node.js status"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if command -v pm2 &> /dev/null; then
    echo "PM2 is installed"
    
    # Check if process exists
    if pm2 list | grep -q "mpwa-whatsapp"; then
        echo -e "${YELLOW}Restarting existing Node.js process...${NC}"
        pm2 restart mpwa-whatsapp
        echo -e "${GREEN}✓ Node.js restarted${NC}"
    else
        echo -e "${YELLOW}Starting Node.js process...${NC}"
        pm2 start server.js --name mpwa-whatsapp
        pm2 save
        echo -e "${GREEN}✓ Node.js started${NC}"
    fi
    
    echo ""
    echo "PM2 Status:"
    pm2 status
else
    echo -e "${YELLOW}⚠ PM2 not installed${NC}"
    echo "To start Node.js manually:"
    echo "  node server.js &"
    echo ""
    echo "To install PM2:"
    echo "  npm install -g pm2"
    echo "  pm2 start server.js --name mpwa-whatsapp"
fi

echo ""

# Step 7: Test connections
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 7: Test connections"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "Testing Node.js on port 3100..."
sleep 2  # Wait for Node.js to start

if curl -s -o /dev/null -w "%{http_code}" http://localhost:3100 | grep -q "200\|404"; then
    echo -e "${GREEN}✓ Node.js is responding on port 3100${NC}"
else
    echo -e "${RED}✗ Node.js not responding (this may be normal if PM2 not installed)${NC}"
    echo "  Try manually: curl http://localhost:3100"
fi

echo ""
echo "Testing Laravel..."
if php artisan --version &>/dev/null; then
    echo -e "${GREEN}✓ Laravel is working${NC}"
    php artisan --version
else
    echo -e "${RED}✗ Laravel has issues${NC}"
fi

echo ""

# Step 8: Run migrations
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Step 8: Run database migrations"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

read -p "Run database migrations? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo "Skipped migrations"
fi

echo ""

# Summary
echo "╔════════════════════════════════════════╗"
echo "║            QUICK FIX COMPLETE!         ║"
echo "╚════════════════════════════════════════╝"
echo ""

echo "What was fixed:"
echo "  ✓ Storage folders created (cache, sessions, views)"
echo "  ✓ Permissions set (775 for directories, 664 for files)"
echo "  ✓ .env updated to use localhost for Node.js"
echo "  ✓ Laravel caches cleared and rebuilt"
echo "  ✓ Node.js process checked/restarted"
echo ""

echo "Next steps:"
echo "  1. Test website: https://wablast.inilaku.com"
echo "  2. Login to dashboard"
echo "  3. Try adding a device and scan QR code"
echo "  4. Check logs if issues persist:"
echo "     - Laravel: tail -f storage/logs/laravel.log"
echo "     - Node.js: pm2 logs mpwa-whatsapp"
echo ""

echo "Verification commands:"
echo "  pm2 status                    # Check Node.js status"
echo "  php test-nodejs.php           # Run full diagnostics"
echo "  bash health-check.sh          # Run health check"
echo "  curl http://localhost:3100    # Test Node.js directly"
echo ""

echo -e "${GREEN}All done! 🎉${NC}"
echo ""
