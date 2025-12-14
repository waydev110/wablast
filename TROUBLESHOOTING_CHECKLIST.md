# 🔧 Production Troubleshooting Checklist

## Server: wablast.inilaku.com

### ✅ Pre-Flight Checks

- [ ] Files uploaded via FTP/cPanel File Manager
- [ ] `.env` file created from `.env.prod`
- [ ] Database credentials verified in `.env`
- [ ] Composer dependencies installed: `composer install --no-dev --optimize-autoloader`
- [ ] NPM dependencies installed: `npm install --production`
- [ ] Storage folders created: `storage/framework/{cache/data,sessions,views}`
- [ ] Permissions set: `chmod 775 storage -R && chmod 775 bootstrap/cache -R`
- [ ] `.htaccess` present in public folder

---

## 🐛 Active Errors & Solutions

### Error 1: Cache Path Error ✓ SOLVED

**Symptom:**
```
InvalidArgumentException: Please provide a valid cache path
```

**Root Cause:** Missing `storage/framework/views` folder

**Solutions (Pick ONE):**

#### Option A: Via Browser (Easiest)
1. Upload `fix-storage.php` to root
2. Visit: `https://wablast.inilaku.com/fix-storage.php`
3. Delete `fix-storage.php` after success

#### Option B: Via SSH
```bash
bash fix-permissions.sh
```

#### Option C: Via cPanel Terminal
```bash
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
chmod 775 storage -R
php artisan cache:clear
```

**Verification:**
```bash
ls -la storage/framework/
# Should show: cache/, sessions/, views/
```

---

### Error 2: Node.js Connection Error ✓ SOLVED

**Symptom:**
```
cURL error 7: Failed to connect to wablast.inilaku.com port 3100
```

**Root Cause:** Shared hosting firewall blocks external access to port 3100

**Solutions (Pick ONE):**

#### Option A: Internal Connection (RECOMMENDED) ⭐
```bash
# Auto-fix via script
bash fix-nodejs-connection.sh

# Or manual:
nano .env
# Change:
WA_URL_SERVER=http://localhost
# Save, then:
php artisan config:clear && php artisan config:cache
```

#### Option B: External Node.js Service
Deploy to Railway.app/Render.com/Fly.io (see `FIX_NODEJS_CONNECTION.md`)

#### Option C: Reverse Proxy
Setup Apache proxy (requires VPS/dedicated, not shared hosting)

**Verification:**
```bash
# Test connection
php test-nodejs.php

# Or manual curl test
curl http://localhost:3100
```

---

## 📋 Step-by-Step Fix Guide

### Step 1: Fix Storage (5 minutes)
```bash
# Via SSH or cPanel Terminal
cd /home/iniz8459/public_html

# Create folders
mkdir -p storage/framework/{cache/data,sessions,views}
touch storage/framework/{cache/data,sessions,views}/.gitkeep

# Set permissions
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;

# Verify
ls -la storage/framework/
```

### Step 2: Fix Node.js Connection (10 minutes)

#### 2.1: Update .env
```bash
nano .env

# Find and change:
WA_URL_SERVER=https://wablast.inilaku.com
# To:
WA_URL_SERVER=http://localhost

# Save (Ctrl+O, Enter, Ctrl+X)
```

#### 2.2: Clear Laravel Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan optimize
```

#### 2.3: Start Node.js Server
```bash
# Check if running
pm2 status

# If not running, start it
pm2 start server.js --name mpwa-whatsapp

# If already running, restart
pm2 restart mpwa-whatsapp

# View logs
pm2 logs mpwa-whatsapp --lines 50
```

#### 2.4: Verify Connection
```bash
# Test 1: Check process
ps aux | grep node

# Test 2: Check port
netstat -tuln | grep 3100

# Test 3: HTTP test
curl http://localhost:3100

# Test 4: Full API test
php test-nodejs.php
```

### Step 3: Database Migration (2 minutes)
```bash
# Option A: Via browser
https://wablast.inilaku.com/migrate

# Option B: Via command line
php artisan migrate --force
```

### Step 4: Final Verification (5 minutes)

#### 4.1: Test Website
```bash
curl -I https://wablast.inilaku.com
# Should return: HTTP/2 200
```

#### 4.2: Test Login
1. Visit: `https://wablast.inilaku.com`
2. Login with credentials
3. Should see dashboard

#### 4.3: Test WhatsApp Connection
1. Go to: Devices → Add Device
2. Fill device details, save
3. Click "Scan" button
4. QR code should appear in 2-5 seconds
5. Scan with WhatsApp
6. Status should change to "Connected"

---

## 🔍 Diagnostic Commands

### Check Application Status
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Node.js logs  
pm2 logs mpwa-whatsapp --lines 100

# Apache/PHP errors
tail -f /var/log/apache2/error.log
# Or cPanel: Error Log viewer
```

### Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
# Should show: PDO object
>>> DB::table('users')->count();
# Should show: number of users
```

### Check Node.js API
```bash
# Test connection endpoint
curl -X POST http://localhost:3100/start-connection \
  -d "device=test123456789"

# Should return JSON response
```

### Check PM2 Status
```bash
pm2 status              # Show all processes
pm2 info mpwa-whatsapp  # Detailed info
pm2 monit               # Live monitoring
pm2 logs --lines 50     # Recent logs
```

---

## 🚨 Common Issues & Quick Fixes

### Issue: QR Code Not Appearing
**Symptoms:** Blank QR area, spinning loader forever

**Fixes:**
1. Check Node.js running: `pm2 status`
2. Check logs: `pm2 logs mpwa-whatsapp`
3. Restart Node: `pm2 restart mpwa-whatsapp`
4. Clear browser cache
5. Check database: `SELECT qr_code, status FROM devices WHERE body = 'DEVICE_NUMBER';`

### Issue: 500 Internal Server Error
**Symptoms:** White page with "500 Server Error"

**Fixes:**
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug: `APP_DEBUG=true` in .env (TEMPORARY!)
3. Clear cache: `php artisan optimize:clear`
4. Check file permissions: `chmod 775 storage -R`
5. Check .htaccess present in public/

### Issue: Database Connection Error
**Symptoms:** "SQLSTATE[HY000] [1045] Access denied"

**Fixes:**
1. Verify credentials in `.env`:
   ```
   DB_DATABASE=iniz8459_wablast
   DB_USERNAME=iniz8459_wablast
   DB_PASSWORD=wablast2025
   ```
2. Test via cPanel phpMyAdmin
3. Clear config: `php artisan config:clear`

### Issue: PM2 Command Not Found
**Symptoms:** `bash: pm2: command not found`

**Fixes:**
```bash
# Install PM2 globally
npm install -g pm2

# Or use npx
npx pm2 start server.js --name mpwa-whatsapp

# Or use nvm
export NVM_DIR="$HOME/.nvm"
source "$NVM_DIR/nvm.sh"
pm2 start server.js
```

### Issue: Port 3100 Already in Use
**Symptoms:** `Error: listen EADDRINUSE: address already in use :::3100`

**Fixes:**
```bash
# Find process using port
lsof -i :3100
# Or
netstat -tuln | grep 3100

# Kill process
kill -9 <PID>

# Restart
pm2 restart mpwa-whatsapp
```

---

## 📊 Health Check Script

Run this command to get full system status:

```bash
# Create health check script
cat > health-check.sh << 'EOF'
#!/bin/bash
echo "=== MPWA Health Check ==="
echo ""
echo "1. Node.js Process:"
ps aux | grep -v grep | grep server.js | head -1 || echo "NOT RUNNING"
echo ""
echo "2. PM2 Status:"
pm2 status 2>/dev/null || echo "PM2 not available"
echo ""
echo "3. Port 3100:"
netstat -tuln 2>/dev/null | grep 3100 || lsof -i :3100 || echo "NOT LISTENING"
echo ""
echo "4. Storage Permissions:"
ls -ld storage/framework/{cache,sessions,views}
echo ""
echo "5. .env Config:"
grep -E "^(WA_URL_SERVER|PORT_NODE|DB_DATABASE)=" .env
echo ""
echo "6. Laravel Status:"
php artisan --version
echo ""
echo "7. Node.js Connection Test:"
curl -s -o /dev/null -w "%{http_code}" http://localhost:3100
echo ""
EOF

chmod +x health-check.sh
bash health-check.sh
```

---

## 📝 Quick Command Reference

| Task | Command |
|------|---------|
| Start Node.js | `pm2 start server.js --name mpwa-whatsapp` |
| Stop Node.js | `pm2 stop mpwa-whatsapp` |
| Restart Node.js | `pm2 restart mpwa-whatsapp` |
| View logs | `pm2 logs mpwa-whatsapp` |
| Clear Laravel cache | `php artisan optimize:clear` |
| Run migrations | `php artisan migrate --force` |
| Start scheduler | `php artisan schedule:run` |
| Start campaign queue | `php artisan start:blast` |
| Test Node.js | `php test-nodejs.php` |
| Fix storage | `bash fix-permissions.sh` |
| Fix Node.js connection | `bash fix-nodejs-connection.sh` |

---

## ✅ Success Criteria

Your application is working correctly when:

- [x] Homepage loads: `https://wablast.inilaku.com` returns 200
- [x] Login successful with credentials
- [x] Dashboard displays without errors
- [x] Node.js process running: `pm2 status` shows "online"
- [x] Node.js accessible: `curl http://localhost:3100` returns response
- [x] QR code appears when clicking "Scan" (within 2-5 seconds)
- [x] WhatsApp connection successful after scanning QR
- [x] Messages can be sent from dashboard
- [x] API endpoints working: `/api/send-message` returns success
- [x] No errors in logs: `storage/logs/laravel.log` clean
- [x] Cron jobs running: Campaign scheduler active

---

## 🆘 Still Having Issues?

### Get More Help:

1. **Check full logs:**
   ```bash
   # Laravel
   tail -100 storage/logs/laravel.log
   
   # Node.js
   pm2 logs mpwa-whatsapp --lines 200 --nostream
   
   # Apache
   tail -100 /home/iniz8459/logs/error_log
   ```

2. **Run diagnostic:**
   ```bash
   php test-nodejs.php > diagnostic-report.txt
   bash health-check.sh >> diagnostic-report.txt
   cat diagnostic-report.txt
   ```

3. **Check specific components:**
   - Database: Visit cPanel → phpMyAdmin
   - Files: cPanel → File Manager → Check permissions
   - PM2: `pm2 info mpwa-whatsapp`
   - Cron: cPanel → Cron Jobs → Verify entries

4. **Common log locations:**
   - Laravel: `storage/logs/laravel.log`
   - PM2: `~/.pm2/logs/`
   - Apache: `/home/iniz8459/logs/error_log`
   - cPanel: Error Log section in dashboard

---

## 📞 Emergency Rollback

If everything fails, rollback to previous version:

```bash
# Backup current (broken) version
mv public_html public_html.broken

# Restore backup
cp -r public_html.backup public_html

# Or re-upload clean files from git

# Reset PM2
pm2 delete mpwa-whatsapp
pm2 start server.js --name mpwa-whatsapp
pm2 save

# Clear all caches
cd public_html
php artisan optimize:clear
php artisan config:cache
```

---

**Document Version:** 1.0  
**Last Updated:** 2025-12-14  
**Server:** wablast.inilaku.com  
**Support:** Check DEPLOYMENT_GUIDE.md for detailed documentation
