# Quick Deployment Checklist

## Pre-Deployment
- [ ] Database created: `iniz8459_wablast`
- [ ] Database user created: `iniz8459_wablast`
- [ ] Database password: `wablast2025`
- [ ] Domain pointed to hosting: `wablast.inilaku.com`
- [ ] SSL certificate installed

## Upload Files
- [ ] Upload all files via FTP/cPanel File Manager
- [ ] Or use Git: `git clone <repo> .`
- [ ] Ensure all files in `public_html/` or appropriate directory

## Configuration
- [ ] Copy `.env.prod` to `.env`
- [ ] Verify database credentials in `.env`
- [ ] Verify `APP_URL=https://wablast.inilaku.com`
- [ ] Verify `WA_URL_SERVER=https://wablast.inilaku.com`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`

## Installation
- [ ] Run: `composer install --optimize-autoloader --no-dev`
- [ ] Run: `npm install --production`
- [ ] Run: `php artisan key:generate`
- [ ] Run: `php artisan migrate --force` OR visit `/migrate`
- [ ] Run: `php artisan storage:link`

## Permissions
- [ ] `storage/` → 755
- [ ] `bootstrap/cache/` → 755
- [ ] `storage/logs/` → 775
- [ ] `credentials/` → 775
- [ ] `.env` → 644

## Cache
- [ ] Run: `php artisan config:cache`
- [ ] Run: `php artisan route:cache`
- [ ] Run: `php artisan view:cache`

## Node.js Server
- [ ] Install PM2: `npm install -g pm2`
- [ ] Start: `pm2 start server.js --name mpwa-whatsapp`
- [ ] Save: `pm2 save`
- [ ] Startup: `pm2 startup`
- [ ] Check: `pm2 status`

## Cron Jobs (cPanel)
Add these in cPanel > Cron Jobs:

```bash
# Laravel Scheduler (every minute)
* * * * * cd /home/iniz8459/public_html && php artisan schedule:run >> /dev/null 2>&1

# Blast Queue (every 5 minutes)
*/5 * * * * cd /home/iniz8459/public_html && php artisan start:blast >> /dev/null 2>&1

# Subscription Check (daily at midnight)
0 0 * * * cd /home/iniz8459/public_html && php artisan subscription:check >> /dev/null 2>&1
```

## Testing
- [ ] Visit: `https://wablast.inilaku.com` → Website loads
- [ ] Visit: `https://wablast.inilaku.com:3100` → Node.js responds
- [ ] Login to dashboard
- [ ] Add new device
- [ ] Click "Scan" → QR code appears (2-5 seconds)
- [ ] Scan with WhatsApp → Status "Connected"
- [ ] Send test message → Message delivered

## Post-Deployment
- [ ] Backup database
- [ ] Backup credentials folder
- [ ] Setup monitoring
- [ ] Configure payment gateways (if needed)
- [ ] Configure SMTP settings
- [ ] Test all features

## Troubleshooting

### QR Code Not Showing
```bash
# Check Node.js
pm2 status
pm2 logs mpwa-whatsapp

# Restart
pm2 restart mpwa-whatsapp

# Check database
mysql -u iniz8459_wablast -p iniz8459_wablast
> SELECT qr_code FROM devices WHERE body='YOUR_NUMBER';
```

### 500 Error
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Check permissions
chmod -R 755 storage bootstrap/cache
```

### Database Connection Failed
- Verify `.env` credentials
- Check database exists
- Test connection: `php artisan tinker` → `DB::connection()->getPdo();`

### Node.js Won't Start
```bash
# Check port
netstat -tuln | grep 3100

# Kill existing process
killall node

# Start again
pm2 start server.js --name mpwa-whatsapp
```

## Important URLs
- **Website**: https://wablast.inilaku.com
- **Migration**: https://wablast.inilaku.com/migrate
- **Cache Clear**: https://wablast.inilaku.com/clear-cache
- **cPanel**: Your hosting cPanel URL

## Support Files
- `DEPLOYMENT_GUIDE.md` - Detailed deployment guide
- `MIGRATION_HTTP_POLLING.md` - HTTP polling migration details
- `.env.prod` - Production environment template

---
**Last Updated**: December 14, 2025
**Server**: wablast.inilaku.com
**Mode**: HTTP Polling (Shared Hosting)
