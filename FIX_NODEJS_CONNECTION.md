# 🔧 QUICK FIX: Node.js Port 3100 Connection Failed

## Error yang Terjadi
```
cURL error 7: Failed to connect to wablast.inilaku.com port 3100
```

## Penyebab
Shared hosting **tidak mengizinkan akses ke port custom** (3100) dari luar. Hanya port 80 (HTTP) dan 443 (HTTPS) yang bisa diakses.

## ✅ Solusi: 3 Opsi

### Opsi 1: Deploy Node.js ke Service Terpisah (RECOMMENDED)

Node.js di shared hosting sangat terbatas. Deploy ke layanan gratis/murah:

#### A. Railway.app (Gratis $5 credit/bulan)
```bash
# 1. Install Railway CLI
npm install -g @railway/cli

# 2. Login
railway login

# 3. Deploy Node.js
cd "g:\xampp\htdocs\Away GateWay"
railway init
railway up

# 4. Link domain atau dapatkan URL
# Contoh: mpwa-node.railway.app
```

Update `.env` di shared hosting:
```env
WA_URL_SERVER=https://mpwa-node.railway.app
PORT_NODE=443
```

#### B. Render.com (Gratis)
1. Push code ke GitHub
2. Login render.com
3. New > Web Service
4. Connect repository
5. Build: `npm install`
6. Start: `node server.js`
7. Environment: `PORT_NODE=3100`

Update `.env`:
```env
WA_URL_SERVER=https://your-app.onrender.com
PORT_NODE=443
```

#### C. Fly.io (Gratis)
```bash
# Install flyctl
curl -L https://fly.io/install.sh | sh

# Deploy
cd "g:\xampp\htdocs\Away GateWay"
flyctl launch
flyctl deploy
```

---

### Opsi 2: Run Node.js di Shared Hosting (Internal Only)

Jika shared hosting support Node.js via cPanel:

#### Update `.env` untuk akses INTERNAL:
```env
# Gunakan localhost, bukan domain
WA_URL_SERVER=http://localhost
PORT_NODE=3100

# ATAU gunakan 127.0.0.1
WA_URL_SERVER=http://127.0.0.1
PORT_NODE=3100

# ATAU gunakan internal IP
WA_URL_SERVER=http://127.0.0.1
PORT_NODE=3100
```

#### Setup di cPanel Node.js App:
1. cPanel > Setup Node.js App
2. Node.js version: 16+
3. Application mode: Production
4. Application root: `/home/iniz8459/public_html/wablast.inilaku.com`
5. Application URL: `wablast.inilaku.com`
6. Application startup file: `server.js`
7. **PENTING**: Set environment variables:
   ```
   DB_HOST=localhost
   DB_DATABASE=iniz8459_wablast
   DB_USERNAME=iniz8459_wablast
   DB_PASSWORD=wablast2025
   PORT_NODE=3100
   ```
8. Click "Run NPM Install"
9. Click "Start Application"

#### Verify Node.js Running (via SSH):
```bash
# Check if Node.js is running
ps aux | grep node

# Check port
netstat -tuln | grep 3100

# Test from server itself
curl http://localhost:3100

# If working, update Laravel .env to use localhost
```

---

### Opsi 3: Reverse Proxy (Advanced)

Jika Node.js berjalan di server yang sama, setup reverse proxy via Apache:

#### 1. Create `.htaccess` di root:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Proxy Node.js requests
    RewriteCond %{REQUEST_URI} ^/node/(.*)$
    RewriteRule ^node/(.*)$ http://localhost:3100/$1 [P,L]
    
    # Redirect to public folder for other requests
    RewriteCond %{REQUEST_URI} !^/node/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### 2. Update Laravel to use proxy:
```php
// app/Http/Controllers/ScanController.php
$response = Http::withOptions(['verify' => false])
    ->timeout(5)
    ->post(env('APP_URL') . '/node/start-connection', [
        'device' => $device
    ]);
```

#### 3. Update `.env`:
```env
WA_URL_SERVER=https://wablast.inilaku.com/node
PORT_NODE=
```

---

## 🚀 Quick Fix Sekarang

Untuk test cepat, gunakan **Opsi 2** dengan internal connection:

### 1. Update `.env` di server:
```bash
# Via SSH atau cPanel File Manager
nano .env

# Ubah:
WA_URL_SERVER=http://localhost
# atau
WA_URL_SERVER=http://127.0.0.1
```

### 2. Restart Node.js:
```bash
# Via PM2
pm2 restart mpwa-whatsapp

# Via cPanel Node.js App
# Stop > Start application
```

### 3. Test dari SSH:
```bash
# Test Node.js dari server
curl http://localhost:3100

# Should return something or error (not connection refused)
```

### 4. Test Laravel connection:
```bash
cd /home/iniz8459/public_html/wablast.inilaku.com
php artisan tinker

>>> use Illuminate\Support\Facades\Http;
>>> $response = Http::post('http://localhost:3100/start-connection', ['device' => '628123456789']);
>>> $response->json();
```

---

## 📋 Checklist Troubleshooting

### Check 1: Node.js Running?
```bash
ps aux | grep node
# Should show server.js process

# If not running:
cd /home/iniz8459/public_html/wablast.inilaku.com
node server.js &
# Or
pm2 start server.js --name mpwa-whatsapp
```

### Check 2: Port 3100 Listening?
```bash
netstat -tuln | grep 3100
# Should show: tcp 0.0.0.0:3100 LISTEN

# If not, check server.js:
cat server.js | grep PORT_NODE
```

### Check 3: Firewall?
```bash
# Test internal connection
curl http://localhost:3100
curl http://127.0.0.1:3100

# Test external (will likely fail on shared hosting)
curl https://wablast.inilaku.com:3100
```

### Check 4: cPanel Node.js App Status
- Login cPanel
- Setup Node.js App
- Check if application is "Running"
- Check "Open Application" button

---

## 🎯 Recommended Solution Berdasarkan Hosting

### Shared Hosting Standard (No SSH):
→ **Deploy Node.js ke Railway/Render** (Opsi 1A/1B)

### Shared Hosting dengan SSH:
→ **Internal localhost connection** (Opsi 2)

### VPS/Cloud:
→ **Setup PM2 dengan reverse proxy** (Opsi 3)

---

## ⚡ Quick Command (Copy-Paste)

### Update .env untuk localhost:
```bash
cd /home/iniz8459/public_html/wablast.inilaku.com
sed -i 's|WA_URL_SERVER=https://wablast.inilaku.com|WA_URL_SERVER=http://localhost|g' .env
php artisan config:clear
php artisan config:cache
```

### Start Node.js (if not running):
```bash
cd /home/iniz8459/public_html/wablast.inilaku.com
pm2 delete mpwa-whatsapp 2>/dev/null
pm2 start server.js --name mpwa-whatsapp
pm2 save
```

### Test connection:
```bash
curl -X POST http://localhost:3100/start-connection -d "device=628123456789"
```

---

## 📞 Next Steps

1. **Pilih solusi yang sesuai hosting Anda**
2. **Update `.env` dengan URL yang benar**
3. **Clear cache**: `php artisan config:clear && php artisan config:cache`
4. **Test koneksi**: Try add device dan scan QR
5. **Check logs**: `storage/logs/laravel.log` dan `pm2 logs mpwa-whatsapp`

---

## 🆘 Jika Masih Error

Contact saya dengan info:
- Hosting provider name
- Apakah ada akses SSH?
- Apakah cPanel punya Node.js App Manager?
- Output dari: `ps aux | grep node`

---

**Quick Win:** Ubah `WA_URL_SERVER=http://localhost` di `.env` dan restart Node.js!
