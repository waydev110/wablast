# Fix Node.js Connection for Shared Hosting (PowerShell)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Fixing Node.js Connection" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Check if .env exists
if (-Not (Test-Path ".env")) {
    Write-Host "Error: .env file not found!" -ForegroundColor Red
    Write-Host "Please copy .env.prod to .env first" -ForegroundColor Yellow
    exit 1
}

Write-Host "Current WA_URL_SERVER:" -ForegroundColor Yellow
Get-Content .env | Select-String "WA_URL_SERVER"

Write-Host ""
Write-Host "Updating to use localhost (internal connection)..." -ForegroundColor Yellow

# Read .env content
$envContent = Get-Content .env

# Update WA_URL_SERVER
$updated = $false
$newContent = @()
foreach ($line in $envContent) {
    if ($line -match "^WA_URL_SERVER=") {
        $newContent += "WA_URL_SERVER=http://localhost"
        $updated = $true
        Write-Host "✓ Updated WA_URL_SERVER to http://localhost" -ForegroundColor Green
    } else {
        $newContent += $line
    }
}

# Add if not exists
if (-not $updated) {
    $newContent += "WA_URL_SERVER=http://localhost"
    Write-Host "✓ Added WA_URL_SERVER=http://localhost" -ForegroundColor Green
}

# Check PORT_NODE
if (-not ($envContent -match "^PORT_NODE=")) {
    $newContent += "PORT_NODE=3100"
    Write-Host "✓ Added PORT_NODE=3100" -ForegroundColor Green
}

# Save updated content
$newContent | Set-Content .env

Write-Host ""
Write-Host "New configuration:" -ForegroundColor Yellow
Get-Content .env | Select-String "WA_URL_SERVER"
Get-Content .env | Select-String "PORT_NODE"

Write-Host ""
Write-Host "Clearing Laravel cache..." -ForegroundColor Yellow
php artisan config:clear 2>$null
php artisan cache:clear 2>$null
php artisan config:cache 2>$null

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Configuration Updated!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Ensure Node.js is running: pm2 status"
Write-Host "2. If not running: pm2 start server.js --name mpwa-whatsapp"
Write-Host "3. Test connection from server: curl http://localhost:3100"
Write-Host "4. Test website: https://wablast.inilaku.com"
Write-Host ""

Read-Host "Press Enter to exit"
