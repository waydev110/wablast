# 🚀 MPWA WhatsApp Gateway - Production Ready

## 📦 Quick Start untuk Deployment

Aplikasi sudah **siap deploy** ke shared hosting dengan konfigurasi HTTP Polling (tanpa WebSocket).

### Server Production
- **URL**: https://wablast.inilaku.com
- **Database**: iniz8459_wablast
- **Mode**: HTTP Polling (Shared Hosting Compatible)

### 📝 File-file Penting

| File | Deskripsi |
|------|-----------|
| `.env.prod` | Template environment untuk production |
| `DEPLOYMENT_GUIDE.md` | Panduan deployment lengkap step-by-step |
| `DEPLOYMENT_CHECKLIST.md` | Checklist cepat untuk deployment |
| `MIGRATION_HTTP_POLLING.md` | Dokumentasi perubahan WebSocket → HTTP Polling |
| `deploy.sh` | Script deployment otomatis (Linux/Mac) |
| `deploy.ps1` | Script deployment otomatis (Windows) |

## 🔧 Quick Deployment (3 Steps)

### Step 1: Upload ke Server
```bash
# Via FTP/cPanel File Manager
# Upload semua file ke public_html/
```

### Step 2: Setup Environment
```bash
# Copy .env.prod ke .env
cp .env.prod .env

# Install dependencies
composer install --no-dev
npm install --production

# Run migration
php artisan migrate --force
# ATAU via browser: https://wablast.inilaku.com/migrate
```

### Step 3: Start Node.js
```bash
# Via SSH dengan PM2
pm2 start server.js --name mpwa-whatsapp
pm2 save

# ATAU via cPanel Node.js App Manager
# Setup Node.js Application di cPanel
```

## ✅ Verifikasi

1. **Website**: https://wablast.inilaku.com ✓
2. **Node.js**: https://wablast.inilaku.com:3100 ✓
3. **Login Dashboard** ✓
4. **Add Device** → **Scan QR** → **Connected** ✓

## 📚 Dokumentasi Lengkap

### Untuk Deployment
- 📖 [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Panduan lengkap
- ✅ [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) - Checklist

### Untuk Development
- 🔄 [MIGRATION_HTTP_POLLING.md](MIGRATION_HTTP_POLLING.md) - Perubahan arsitektur
- 📝 [.github/copilot-instructions.md](.github/copilot-instructions.md) - AI agent guide

## 🎯 Fitur HTTP Polling

✅ **Kompatibel dengan Shared Hosting**
- Tidak perlu WebSocket support
- Tidak perlu konfigurasi port khusus
- QR code tersimpan di database
- Polling setiap 2 detik (acceptable delay)

✅ **Semua Fitur Berjalan Normal**
- Send text, media, button, template, list, poll
- Auto-reply system
- Campaign/blast
- API endpoints
- Multi-device
- Multi-instance

## 🔒 Keamanan Production

File `.env.prod` sudah dikonfigurasi dengan:
- `APP_DEBUG=false`
- `APP_ENV=production`
- `LOG_LEVEL=error`
- Database credentials secure
- SSL ready

## 🛠 Troubleshooting

### QR Code tidak muncul?
```bash
# Check Node.js status
pm2 status

# Restart Node.js
pm2 restart mpwa-whatsapp

# Check logs
pm2 logs mpwa-whatsapp
```

### 500 Error?
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Check logs
tail -f storage/logs/laravel.log
```

### Database error?
- Verify `.env` database credentials
- Run migration: `php artisan migrate --force`

## 📞 Support

Lihat file-file dokumentasi untuk panduan lengkap. Semua troubleshooting dan FAQ sudah ada di:
- [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
- [MIGRATION_HTTP_POLLING.md](MIGRATION_HTTP_POLLING.md)

## 📊 Requirements

### Server
- PHP 8.0+
- MySQL 5.7+
- Node.js 16.x+
- Composer
- SSL Certificate

### PHP Extensions
- BCMath, Ctype, Fileinfo, JSON, Mbstring
- OpenSSL, PDO, Tokenizer, XML, cURL
- GD Library, ionCube Loader

## 🎨 Themes Available
- `mpwa` (default)
- `eres`
- `erescompact`
- `lezir` (index page)

## 💳 Payment Gateways Support
- Stripe
- PayPal
- Midtrans
- Paymob

Configure via `.env` file.

## 🌍 Multi-Language Support
- English (default)
- Indonesian
- Add more via Laravel Localization

---

**Version**: 9.6.1 (HTTP Polling)
**License**: CC BY-NC-ND 4.0
**Author**: Magd Almuntaser, OneXGen Technology
**Modified**: December 14, 2025 (HTTP Polling Implementation)

## 🚀 Deploy Now!

Ikuti [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) untuk deployment cepat.

Good luck! 🎉
