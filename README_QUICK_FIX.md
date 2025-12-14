# 🚀 Quick Production Fix Guide

**Server:** wablast.inilaku.com  
**Status:** 2 errors need fixing

---

## ⚡ One-Click Fix (RECOMMENDED)

Upload `quick-fix.sh` to server, then run:

```bash
chmod +x quick-fix.sh
bash quick-fix.sh
```

This automatically fixes BOTH errors (cache path + Node.js connection).

---

## 🔧 Manual Fix (Alternative)

If you prefer manual fixes:

### Fix 1: Storage Error (2 minutes)

```bash
mkdir -p storage/framework/{cache/data,sessions,views}
chmod 775 storage -R
php artisan cache:clear
```

### Fix 2: Node.js Connection (3 minutes)

```bash
nano .env
# Change: WA_URL_SERVER=http://localhost
# Save: Ctrl+O, Enter, Ctrl+X

php artisan config:clear
php artisan config:cache
pm2 restart mpwa-whatsapp
```

---

## ✅ Verify Everything Works

After fixes, test:

```bash
# Quick test
php public/test-nodejs.php

# Or visit browser
https://wablast.inilaku.com/test-nodejs.php
```

Expected result: All tests pass ✅

---

## 📱 Test WhatsApp Connection

1. Login: `https://wablast.inilaku.com`
2. Go to: Devices → Add Device
3. Fill form, click Save
4. Click "Scan" button
5. QR code appears in 2-5 seconds ✅
6. Scan with WhatsApp
7. Status changes to "Connected" ✅

---

## 🆘 Still Having Issues?

Run diagnostics:

```bash
bash health-check.sh
```

Or check detailed guide: `TROUBLESHOOTING_CHECKLIST.md`

---

## 📋 File Overview

| File | Purpose | Usage |
|------|---------|-------|
| `quick-fix.sh` | **One-click fix (both errors)** | `bash quick-fix.sh` ⭐ |
| `fix-storage.php` | Fix cache path (web) | Visit in browser |
| `fix-permissions.sh` | Fix cache path (SSH) | `bash fix-permissions.sh` |
| `fix-nodejs-connection.sh` | Fix Node.js only | `bash fix-nodejs-connection.sh` |
| `test-nodejs.php` | Test connection | `php public/test-nodejs.php` (CLI) or visit in browser |
| `health-check.sh` | Full diagnostics | `bash health-check.sh` |
| `TROUBLESHOOTING_CHECKLIST.md` | Detailed guide | Read for complex issues |
| `DEPLOYMENT_GUIDE.md` | Full deployment docs | Complete reference |

---

## 🎯 Quick Command Reference

```bash
# Start Node.js
pm2 start server.js --name mpwa-whatsapp

# Restart Node.js
pm2 restart mpwa-whatsapp

# Check status
pm2 status

# View logs
pm2 logs mpwa-whatsapp

# Clear cache
php artisan optimize:clear

# Run migrations
php artisan migrate --force
```

---

## 📞 Support

- Documentation: See `DEPLOYMENT_GUIDE.md`
- Checklist: See `TROUBLESHOOTING_CHECKLIST.md`
- Errors: Check `storage/logs/laravel.log`
- Node.js logs: `pm2 logs mpwa-whatsapp`

---

**Last Updated:** 2025-12-14  
**Version:** 1.0
