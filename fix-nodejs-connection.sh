#!/bin/bash

# Fix Node.js Connection for Shared Hosting
# This script configures .env to use localhost instead of external URL

echo "======================================"
echo "Fixing Node.js Connection"
echo "======================================"
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "Error: .env file not found!"
    echo "Please copy .env.prod to .env first"
    exit 1
fi

echo "Current WA_URL_SERVER:"
grep WA_URL_SERVER .env

echo ""
echo "Updating to use localhost (internal connection)..."

# Update WA_URL_SERVER to localhost
if grep -q "WA_URL_SERVER=" .env; then
    sed -i.bak 's|WA_URL_SERVER=.*|WA_URL_SERVER=http://localhost|g' .env
    echo "✓ Updated WA_URL_SERVER to http://localhost"
else
    echo "WA_URL_SERVER=http://localhost" >> .env
    echo "✓ Added WA_URL_SERVER=http://localhost"
fi

# Ensure PORT_NODE is set
if ! grep -q "PORT_NODE=" .env; then
    echo "PORT_NODE=3100" >> .env
    echo "✓ Added PORT_NODE=3100"
fi

echo ""
echo "New configuration:"
grep WA_URL_SERVER .env
grep PORT_NODE .env

echo ""
echo "Clearing Laravel cache..."
php artisan config:clear 2>/dev/null
php artisan cache:clear 2>/dev/null
php artisan config:cache 2>/dev/null

echo ""
echo "======================================"
echo "Configuration Updated!"
echo "======================================"
echo ""
echo "Next steps:"
echo "1. Ensure Node.js is running: pm2 status"
echo "2. If not running: pm2 start server.js --name mpwa-whatsapp"
echo "3. Test connection: curl http://localhost:3100"
echo "4. Test website: https://wablast.inilaku.com"
echo ""
echo "To verify Node.js is accessible:"
echo "  php artisan tinker"
echo "  >>> Http::post('http://localhost:3100/start-connection', ['device' => '123'])"
echo ""
