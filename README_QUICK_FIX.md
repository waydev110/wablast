# 🚀 Quick Production Fix Guide

**Server:** wablast.inilaku.com  
**Status:** 2 errors need fixing

---

## ⚡ Quick Fixes

### Option A: Via Web Browser (Tanpa SSH) 🌐

**Paling mudah untuk shared hosting!**

#### Prerequisites: Setup Node.js di Subdomain

**PENTING:** Node.js harus di subdomain terpisah (baca: `SETUP_NODEJS_SUBDOMAIN.md`)

Contoh:
- Laravel: `https://wablast.inilaku.com`
- Node.js: `https://node.wablast.inilaku.com` ⭐

---

#### Quick Fix Steps:

1. **Upload 3 files ke folder `public/`:**
   - `fix-storage.php` → `public/fix-storage.php`
   - `fix-env.php` → `public/fix-env.php` ⭐
   - `test-nodejs.php` → `public/test-nodejs.php`

2. **Fix Error #1 (Storage):**
   ```
   https://wablast.inilaku.com/fix-storage.php
   ```
   Klik tombol "Fix Storage" → Done ✅

3. **Fix Error #2 (Node.js Connection):**
   ```
   https://wablast.inilaku.com/fix-env.php
   ```
   Input Node.js URL: `https://node.wablast.inilaku.com`
   Klik "Update .env Configuration" → Done ✅

4. **Clear cache:**
   ```
   https://wablast.inilaku.com/clear-cache
   ```

5. **Test everything:**
   ```
   https://wablast.inilaku.com/test-nodejs.php
   ```
   Semua test harus PASS ✅

**Total waktu: 5 menit!** 🎉

---

### Option B: Via SSH (One-Click Fix) 🚀

Upload `quick-fix.sh` ke root, then run:

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

| File | Location | Purpose | Usage |
|------|----------|---------|-------|
| `fix-env.php` | `public/` | **Fix .env (Node.js)** | Visit `https://wablast.inilaku.com/fix-env.php` ⭐⭐⭐ |
| `fix-storage.php` | `public/` | Fix cache path | Visit `https://wablast.inilaku.com/fix-storage.php` ⭐⭐ |
| `test-nodejs.php` | `public/` | Test connection | Visit `https://wablast.inilaku.com/test-nodejs.php` ⭐ |
| `quick-fix.sh` | Root | One-click fix (SSH) | `bash quick-fix.sh` |
| `fix-permissions.sh` | Root | Fix cache path (SSH) | `bash fix-permissions.sh` |
| `fix-nodejs-connection.sh` | Root | Fix Node.js (SSH) | `bash fix-nodejs-connection.sh` |
| `health-check.sh` | Root | Full diagnostics | `bash health-check.sh` |
| `FILE_STRUCTURE.md` | Docs | File organization | Read this |
| `TROUBLESHOOTING_CHECKLIST.md` | Docs | Detailed guide | Complex issues |
| `DEPLOYMENT_GUIDE.md` | Docs | Full deployment | Complete reference |

**Note:** Files di `public/` bisa diakses via browser (tanpa SSH). Files di root hanya via SSH.

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
