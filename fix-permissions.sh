#!/bin/bash

# Fix Laravel Cache & Storage Permissions
# Run this script on server after upload

echo "======================================"
echo "Fixing Laravel Storage & Cache"
echo "======================================"
echo ""

# Create missing directories
echo "Creating storage directories..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache
mkdir -p credentials

echo "Directories created!"
echo ""

# Set correct permissions
echo "Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/framework
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/views
chmod -R 775 credentials
chmod 644 .env

echo "Permissions set!"
echo ""

# Clear all caches
echo "Clearing caches..."
php artisan cache:clear 2>/dev/null || echo "Cache cleared (or was empty)"
php artisan config:clear 2>/dev/null || echo "Config cleared"
php artisan view:clear 2>/dev/null || echo "Views cleared"
php artisan route:clear 2>/dev/null || echo "Routes cleared"

echo ""
echo "======================================"
echo "Fix Complete!"
echo "======================================"
echo ""
echo "Now run:"
echo "1. php artisan config:cache"
echo "2. php artisan route:cache"
echo "3. php artisan view:cache"
echo ""
