# MPWA WhatsApp Gateway - Production Deployment Script (Windows)
# Server: wablast.inilaku.com
# Mode: HTTP Polling (Shared Hosting Compatible)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "MPWA Production Deployment" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Check if running in correct directory
if (-Not (Test-Path "artisan")) {
    Write-Host "Error: artisan file not found. Please run this script from Laravel root directory." -ForegroundColor Red
    exit 1
}

Write-Host "Step 1: Setup Environment" -ForegroundColor Yellow
if (Test-Path ".env") {
    $backup = Read-Host ".env already exists. Backup? (y/n)"
    if ($backup -eq "y") {
        $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
        Copy-Item .env ".env.backup.$timestamp"
        Write-Host "Backup created" -ForegroundColor Green
    }
}

$copy_env = Read-Host "Copy .env.prod to .env? (y/n)"
if ($copy_env -eq "y") {
    Copy-Item .env.prod .env
    Write-Host ".env created from .env.prod" -ForegroundColor Green
} else {
    Write-Host "Skipped" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Step 2: Install Dependencies" -ForegroundColor Yellow
Write-Host "Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

Write-Host "Installing NPM dependencies..."
npm install --production

Write-Host ""
Write-Host "Step 3: Generate Application Key" -ForegroundColor Yellow
php artisan key:generate

Write-Host ""
Write-Host "Step 4: Run Migrations" -ForegroundColor Yellow
$run_migrate = Read-Host "Run migrations? (y/n)"
if ($run_migrate -eq "y") {
    php artisan migrate --force
    Write-Host "Migrations completed" -ForegroundColor Green
} else {
    Write-Host "Skipped - You can run manually: php artisan migrate" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Step 5: Storage Link" -ForegroundColor Yellow
php artisan storage:link

Write-Host ""
Write-Host "Step 6: Cache Configuration" -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache
Write-Host "Caches created" -ForegroundColor Green

Write-Host ""
Write-Host "Step 7: Node.js Server" -ForegroundColor Yellow
Write-Host "Starting Node.js server with PM2..."
$pm2Installed = Get-Command pm2 -ErrorAction SilentlyContinue
if ($pm2Installed) {
    pm2 delete mpwa-whatsapp 2>$null
    pm2 start server.js --name "mpwa-whatsapp"
    pm2 save
    Write-Host "Node.js server started" -ForegroundColor Green
} else {
    Write-Host "PM2 not found. Please install: npm install -g pm2" -ForegroundColor Red
    Write-Host "Or start manually: node server.js" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Deployment Complete!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:"
Write-Host "1. Upload to server via FTP/cPanel"
Write-Host "2. Configure cron jobs (see DEPLOYMENT_GUIDE.md)"
Write-Host "3. Test website: https://wablast.inilaku.com"
Write-Host "4. Test Node.js: https://wablast.inilaku.com:3100"
Write-Host "5. Add WhatsApp device and test QR code scanning"
Write-Host ""
Write-Host "Logs:"
Write-Host "- Laravel: storage/logs/laravel.log"
Write-Host "- Node.js: pm2 logs mpwa-whatsapp"
Write-Host ""
Write-Host "Management Commands:"
Write-Host "- Check status: pm2 status"
Write-Host "- View logs: pm2 logs mpwa-whatsapp"
Write-Host "- Restart: pm2 restart mpwa-whatsapp"
Write-Host ""

Read-Host "Press Enter to exit"
