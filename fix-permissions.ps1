# Fix Laravel Cache & Storage Permissions (Windows/PowerShell)
# Run this script on server after upload

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Fixing Laravel Storage & Cache" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Create missing directories
Write-Host "Creating storage directories..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "storage/framework/cache/data" | Out-Null
New-Item -ItemType Directory -Force -Path "storage/framework/sessions" | Out-Null
New-Item -ItemType Directory -Force -Path "storage/framework/views" | Out-Null
New-Item -ItemType Directory -Force -Path "storage/logs" | Out-Null
New-Item -ItemType Directory -Force -Path "storage/app/public" | Out-Null
New-Item -ItemType Directory -Force -Path "bootstrap/cache" | Out-Null
New-Item -ItemType Directory -Force -Path "credentials" | Out-Null

Write-Host "Directories created!" -ForegroundColor Green
Write-Host ""

# Clear all caches
Write-Host "Clearing caches..." -ForegroundColor Yellow
php artisan cache:clear 2>$null
php artisan config:clear 2>$null
php artisan view:clear 2>$null
php artisan route:clear 2>$null

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Fix Complete!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now run:"
Write-Host "1. php artisan config:cache"
Write-Host "2. php artisan route:cache"
Write-Host "3. php artisan view:cache"
Write-Host ""
Write-Host "If running on shared hosting (cPanel), set permissions via File Manager:"
Write-Host "- storage/ -> 755"
Write-Host "- storage/framework/views -> 775"
Write-Host "- bootstrap/cache -> 755"
Write-Host ""

Read-Host "Press Enter to exit"
