# Node.js Deployment untuk Shared Hosting

## 📦 Struktur Folder
```
nodejs-deployment/
├── server.js              # Entry point aplikasi
├── package.json           # Dependencies
├── .env.example          # Template environment variables
├── server/
│   ├── whatsapp.js       # WhatsApp connection handler (obfuscated)
│   ├── polling-helper.js # QR code save functions
│   ├── controllers/      # Request handlers
│   ├── database/         # MySQL connection pool
│   ├── lib/              # Helper functions
│   └── router/           # Express routes
└── credentials/          # WhatsApp session storage (auto-generated)
```

## 🚀 Deployment ke cPanel

### 1. Upload Files
Upload semua file di folder `nodejs-deployment/` ke:
```
/home/iniz8459/public_html/node.wablast.inilaku.com/
```

**Yang di-upload:**
- ✅ server.js
- ✅ package.json
- ✅ server/ (folder lengkap dengan isinya)
- ✅ credentials/ (folder kosong)
- ❌ JANGAN upload .env (pakai environment variables di cPanel)

### 2. Setup di cPanel Node.js Selector

1. Login cPanel → **Setup Node.js App**
2. Klik aplikasi **node.wablast.inilaku.com**
3. Set konfigurasi:
   - **Node.js version**: 22.18.0
   - **Application mode**: production
   - **Application root**: public_html/node.wablast.inilaku.com
   - **Application URL**: node.wablast.inilaku.com
   - **Application startup file**: server.js
   - **Passenger log file**: logs/passenger.log

4. **Environment Variables** (klik "Add variable"):
   ```
   PORT_NODE = 3100
   DB_HOST = localhost
   DB_USERNAME = iniz8459_wablast
   DB_DATABASE = iniz8459_wablast
   DB_PASSWORD = wablast2025
   ```

5. Klik **"Run NPM Install"** (tunggu selesai)
6. Klik **"Start App"** (status harus hijau)

### 3. Test Endpoints

**Base URL Check:**
```
GET https://node.wablast.inilaku.com/
```
Expected: "It works! NodeJS 22.18.0"

**Start Connection:**
```
POST https://node.wablast.inilaku.com/start-connection
Content-Type: application/x-www-form-urlencoded

device=628123456789
```
Expected: `{"status":true,"msg":"Connection initiated"}`

**Poll QR Code:**
```
GET https://node.wablast.inilaku.com/poll-connection/628123456789
```
Expected: JSON dengan `qr_code` field

### 4. Troubleshooting

**Error 503 (Service Unavailable):**
- App crash atau tidak bisa start
- Cek logs: cPanel → Node.js App → "Show logs"
- Cek syntax error atau missing dependencies

**Error 400 (Bad Request):**
- URL salah atau routing issue
- Pastikan Application Startup File = `server.js`
- Restart app

**QR Code Null:**
- Database connection error
- Cek environment variables sudah benar
- Cek DB credentials sama dengan Laravel

**"Cannot find module":**
- NPM install belum selesai
- Jalankan lagi "Run NPM Install" di cPanel

### 5. Restart App
Setiap kali update code:
1. Upload file baru
2. cPanel → Node.js App → **Stop App**
3. **Start App**
4. Cek status hijau

### 6. View Logs
**Via cPanel:**
- Setup Node.js App → klik app → "Show logs"

**Via SSH:**
```bash
cd ~/public_html/node.wablast.inilaku.com
cat logs/passenger.log
```

## 🔗 Integration dengan Laravel

Laravel di `wablast.inilaku.com` akan hit ke Node.js:
```
Browser → Laravel (polling) → Node.js (generate QR) → Database
```

Pastikan di Laravel `.env`:
```
WA_URL_SERVER=https://node.wablast.inilaku.com
PORT_NODE=
```
(PORT_NODE dikosongkan karena pakai subdomain, bukan localhost:port)

## ✅ Checklist Deployment
- [ ] Upload semua file ke server
- [ ] Set environment variables di cPanel
- [ ] Run NPM Install
- [ ] Application Startup File = server.js
- [ ] Start app (status hijau)
- [ ] Test GET / (dapat "It works!")
- [ ] Test POST /start-connection (dapat JSON)
- [ ] Test GET /poll-connection/:device (dapat qr_code)
- [ ] Check logs tidak ada error
