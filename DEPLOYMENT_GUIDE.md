# Panduan Deployment ke Shared Hosting

## Informasi Server Production
- **URL**: https://wablast.inilaku.com
- **Database**: iniz8459_wablast
- **User**: iniz8459_wablast
- **Password**: wablast2025
- **Mode**: HTTP Polling (Shared Hosting Compatible)

## Persiapan Pre-Deployment

### 1. Requirements
- PHP 8.0 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Node.js 16.x atau lebih tinggi (biasanya via SSH atau alternative)
- Composer
- SSL Certificate (sudah tersedia untuk https://wablast.inilaku.com)

### 2. Ekstensi PHP yang Dibutuhkan
```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL
- GD Library
- ionCube Loader (untuk WhatsappServiceImpl.php)
```

## Langkah-Langkah Deployment

### Step 1: Upload File ke Shared Hosting

#### Via FTP/cPanel File Manager:
```
1. Compress semua file menjadi .zip
2. Upload ke public_html/ atau subdirectory
3. Extract di server
```

#### Via Git (jika shared hosting support):
```bash
cd public_html
git clone <repository-url> .
```

### Step 2: Setup Environment

```bash
# Copy .env.prod ke .env
cp .env.prod .env

# ATAU via cPanel File Manager:
# - Rename .env.prod menjadi .env
```

### Step 3: Install Dependencies

#### Via SSH (jika tersedia):
```bash
cd public_html
composer install --optimize-autoloader --no-dev
npm install --production
```

#### Via cPanel:
Jika tidak ada akses SSH, bisa:
1. Install dependencies di local
2. Upload folder `vendor/` dan `node_modules/` via FTP
3. ATAU gunakan composer.phar:
```bash
php composer.phar install --optimize-autoloader --no-dev
```

### Step 4: Setup Application

```bash
# Generate application key (jika belum ada di .env)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Clear & cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions (via SSH)
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
```

#### Via cPanel:
Akses URL: `https://wablast.inilaku.com/migrate` untuk run migrations

### Step 5: Setup Node.js Server

Ini adalah bagian **PENTING** - Node.js harus jalan untuk WhatsApp gateway bekerja.

#### Opsi A: Via SSH (Recommended)
```bash
cd public_html

# Install PM2 untuk keep Node.js running
npm install -g pm2

# Start server
pm2 start server.js --name "mpwa-whatsapp"

# Auto-start on server reboot
pm2 startup
pm2 save

# Check status
pm2 status
pm2 logs mpwa-whatsapp
```

#### Opsi B: Via cPanel Node.js Application
```
1. Login cPanel
2. Go to "Setup Node.js App"
3. Create New Application:
   - Node.js version: 16.x atau lebih tinggi
   - Application mode: Production
   - Application root: public_html
   - Application URL: wablast.inilaku.com
   - Application startup file: server.js
   - Environment variables: 
     * PORT_NODE=3100
     * DB_HOST=localhost
     * DB_DATABASE=iniz8459_wablast
     * DB_USERNAME=iniz8459_wablast
     * DB_PASSWORD=wablast2025
4. Save & Run NPM Install
5. Start Application
```

#### Opsi C: Alternative Port Forwarding
Jika shared hosting tidak support Node.js:
1. Deploy Node.js ke layanan terpisah:
   - Heroku (free tier)
   - Railway.app
   - Render.com
   - VPS murah ($5/bulan)
2. Update `.env`:
```
WA_URL_SERVER=https://your-nodejs-server.herokuapp.com
PORT_NODE=443
```

### Step 6: Setup Database

Database sudah dibuat oleh hosting, tinggal run migration:
```bash
# Via SSH
php artisan migrate --force

# Via Browser
https://wablast.inilaku.com/migrate
```

### Step 7: Setup Cron Job

Login cPanel > Cron Jobs > Add New:

```bash
# Run Laravel scheduler every minute
* * * * * cd /home/iniz8459/public_html && php artisan schedule:run >> /dev/null 2>&1

# Run blast queue every 5 minutes
*/5 * * * * cd /home/iniz8459/public_html && php artisan start:blast >> /dev/null 2>&1

# Check subscriptions daily at midnight
0 0 * * * cd /home/iniz8459/public_html && php artisan subscription:check >> /dev/null 2>&1

# Cleanup QR codes every 5 minutes (optional)
*/5 * * * * cd /home/iniz8459/public_html && php artisan qrcode:cleanup >> /dev/null 2>&1
```

### Step 8: Setup SSL Certificate

Biasanya cPanel/shared hosting sudah auto-install SSL untuk domain. Jika belum:

```
1. Login cPanel
2. Go to "SSL/TLS"
3. Click "Manage SSL sites"
4. Install Let's Encrypt (Free) untuk wablast.inilaku.com
```

### Step 9: Konfigurasi Web Server

#### .htaccess (Laravel)
File `.htaccess` di root harus redirect ke `public/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### public/.htaccess
Pastikan file ini ada dan berisi:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Step 10: File Permissions

```bash
# Via SSH
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 credentials
chmod 644 .env

# Via cPanel File Manager:
# Right click folder > Change Permissions
# storage: 755
# bootstrap/cache: 755
# .env: 644
```

### Step 11: Testing

1. **Test Website**:
   - Buka https://wablast.inilaku.com
   - Login dengan credentials default atau buat user baru

2. **Test Node.js Server**:
   ```bash
   curl https://wablast.inilaku.com:3100
   # ATAU via browser
   https://wablast.inilaku.com:3100
   ```

3. **Test WhatsApp Connection**:
   - Login ke dashboard
   - Add device baru
   - Klik "Scan" untuk QR code
   - QR code harus muncul dalam 2-5 detik
   - Scan dengan WhatsApp
   - Status berubah "Connected"

4. **Test Message Sending**:
   - Send test message via UI atau API
   - Message harus terkirim

## Troubleshooting

### Error: "Failed to connect to Node.js server"

**Solusi 1 - Check Node.js Running:**
```bash
pm2 status
# Jika tidak running:
pm2 start server.js --name mpwa-whatsapp
```

**Solusi 2 - Check Port:**
```bash
netstat -tuln | grep 3100
# Jika tidak ada, start server
```

**Solusi 3 - Firewall:**
```
- Login cPanel
- Open port 3100 di firewall
- ATAU contact hosting support
```

### Error: "QR Code not showing"

**Check:**
1. Node.js server running
2. Database migration sudah jalan
3. Browser console untuk error AJAX
4. Network tab - polling request berhasil

**Fix:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart Node.js
pm2 restart mpwa-whatsapp
```

### Error: "500 Internal Server Error"

**Check:**
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Common issues:
# 1. Permission issues
chmod -R 755 storage bootstrap/cache

# 2. .env not configured
cp .env.prod .env

# 3. Cache issues
php artisan config:clear
php artisan cache:clear
```

### Error: "Database connection failed"

**Check `.env`:**
```
DB_HOST=localhost
DB_DATABASE=iniz8459_wablast
DB_USERNAME=iniz8459_wablast
DB_PASSWORD=wablast2025
```

**Test connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Node.js Keeps Stopping

**Solusi menggunakan PM2:**
```bash
pm2 start server.js --name mpwa-whatsapp
pm2 startup
pm2 save

# View logs
pm2 logs mpwa-whatsapp

# Monitor
pm2 monit
```

## Post-Deployment Checklist

- [ ] Website accessible via https://wablast.inilaku.com
- [ ] Node.js server running on port 3100
- [ ] Database migrations completed
- [ ] Cron jobs configured
- [ ] SSL certificate active
- [ ] File permissions correct
- [ ] QR code polling working
- [ ] Message sending working
- [ ] Webhook configured (if needed)
- [ ] Payment gateways configured (if using)
- [ ] Email settings configured
- [ ] Backup strategy in place

## Maintenance

### Daily Tasks:
```bash
# Check Node.js status
pm2 status

# Check logs for errors
tail -f storage/logs/laravel.log
pm2 logs mpwa-whatsapp --lines 100
```

### Weekly Tasks:
```bash
# Cleanup old QR codes
php artisan qrcode:cleanup

# Optimize database
php artisan db:optimize
```

### Monthly Tasks:
```bash
# Update dependencies
composer update
npm update

# Backup database
mysqldump -u iniz8459_wablast -p iniz8459_wablast > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d).tar.gz public_html/
```

## Backup Strategy

### Database Backup:
```bash
# Via cPanel > phpMyAdmin > Export
# ATAU via SSH:
mysqldump -u iniz8459_wablast -p iniz8459_wablast > /home/backup/db_$(date +%Y%m%d).sql
```

### File Backup:
```bash
# Backup entire application
tar -czf mpwa_backup_$(date +%Y%m%d).tar.gz /home/iniz8459/public_html

# Backup hanya credentials (important!)
tar -czf credentials_backup_$(date +%Y%m%d).tar.gz /home/iniz8459/public_html/credentials
```

### Restore:
```bash
# Restore database
mysql -u iniz8459_wablast -p iniz8459_wablast < backup_20251214.sql

# Restore files
tar -xzf mpwa_backup_20251214.tar.gz
```

## Security Checklist

- [ ] `APP_DEBUG=false` di production
- [ ] `.env` file permissions 644
- [ ] `storage/` dan `bootstrap/cache/` tidak accessible via web
- [ ] SSL certificate active
- [ ] Strong database password
- [ ] API keys secure (tidak di-commit ke git)
- [ ] Regular security updates
- [ ] Firewall configured
- [ ] Rate limiting enabled
- [ ] CSRF protection enabled

## Support

Jika ada masalah:
1. Check logs: `storage/logs/laravel.log`
2. Check Node.js: `pm2 logs mpwa-whatsapp`
3. Contact hosting support untuk Node.js issues
4. Refer to `MIGRATION_HTTP_POLLING.md` untuk polling issues

---

**Deployment Date:** December 14, 2025
**Server:** wablast.inilaku.com
**Mode:** HTTP Polling (Shared Hosting Compatible)
