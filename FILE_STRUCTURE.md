# 📂 Production Files Structure

## File Organization

### 📁 Root Directory (CLI Scripts)
Files yang dijalankan via **SSH/Terminal**:

```
/home/iniz8459/public_html/
├── quick-fix.sh                    ⭐ One-click fix (run via SSH)
├── fix-permissions.sh              Fix storage permissions (SSH)
├── fix-permissions.ps1             Fix storage permissions (PowerShell)
├── fix-nodejs-connection.sh        Fix Node.js config (SSH)
├── fix-nodejs-connection.ps1       Fix Node.js config (PowerShell)
├── health-check.sh                 System diagnostics (SSH)
├── server.js                       Node.js WhatsApp server
├── .env                            Configuration (NEVER commit!)
├── .env.prod                       Production template
└── [Laravel files...]
```

**Usage:**
```bash
cd /home/iniz8459/public_html
chmod +x quick-fix.sh
bash quick-fix.sh
```

---

### 📁 Public Directory (Web-Accessible)
Files yang diakses via **Browser**:

```
/home/iniz8459/public_html/public/
├── index.php                       Laravel entry point
├── test-nodejs.php                 ⭐ Node.js connection test (web)
├── fix-storage.php                 ⭐ Storage fix tool (web)
├── .htaccess                       Apache rewrite rules
└── [Assets: css, js, images...]
```

**URLs:**
- https://wablast.inilaku.com → `public/index.php` (Laravel)
- https://wablast.inilaku.com/test-nodejs.php → `public/test-nodejs.php`
- https://wablast.inilaku.com/fix-storage.php → `public/fix-storage.php`

---

## 🎯 Quick Reference

### Via Web Browser (No SSH Required)

```
✅ Test Connection:
https://wablast.inilaku.com/test-nodejs.php

✅ Fix Storage:
https://wablast.inilaku.com/fix-storage.php
```

**Advantages:**
- No SSH access needed
- Works on shared hosting
- Easy for non-technical users
- Instant results in browser

---

### Via SSH/Terminal (Full Control)

```bash
# One-click fix everything
bash quick-fix.sh

# Or individual fixes:
bash fix-permissions.sh           # Fix storage
bash fix-nodejs-connection.sh     # Fix Node.js
bash health-check.sh              # Diagnostics

# Test from CLI
php public/test-nodejs.php
```

**Advantages:**
- More control over process
- Can see detailed output
- Can automate with scripts
- Can run background processes

---

## 📋 Deployment Checklist

### Architecture: Separated Servers

```
Laravel Server:    /home/iniz8459/public_html/
Node.js Server:    /home/iniz8459/node_app/  (atau subdomain terpisah)
```

### Step 1: Upload Laravel Files

**Root directory** (via FTP to `/home/iniz8459/public_html/`):
- [ ] All Laravel files (app, config, routes, etc.)
- [ ] `.env` (copy from `.env.prod`)
- [ ] `quick-fix.sh`, `fix-permissions.sh`, `health-check.sh`
- [ ] **JANGAN upload `server.js` di sini!** ⚠️

**Public directory** (via FTP to `/home/iniz8459/public_html/public/`):
- [ ] `test-nodejs.php`
- [ ] `fix-storage.php`
- [ ] `fix-env.php` ⭐
- [ ] `index.php` (Laravel)
- [ ] `.htaccess`

### Step 2: Upload Node.js Files (Separate Location)

**Node.js directory** (via FTP to `/home/iniz8459/node_app/`):
- [ ] `server.js`
- [ ] `package.json`
- [ ] `package-lock.json`
- [ ] `server/` folder (whatsapp.js, controllers, router, database, lib)
- [ ] `credentials/` folder (empty, untuk session)

**Setup via cPanel Node.js Selector** (baca: `SETUP_NODEJS_SUBDOMAIN.md`)

### Step 2: Set Permissions

```bash
# Via SSH
cd /home/iniz8459/public_html
chmod +x *.sh
chmod 644 .env
chmod 644 public/test-nodejs.php
chmod 644 public/fix-storage.php
```

### Step 3: Choose Fix Method

**Option A: Web (Easiest)**
1. Visit: https://wablast.inilaku.com/fix-storage.php
2. Visit: https://wablast.inilaku.com/test-nodejs.php
3. Check all tests pass ✅

**Option B: SSH (Recommended)**
```bash
bash quick-fix.sh
```

---

## 🔒 Security Notes

### Files in `public/` Directory

**Safe for public access:**
- ✅ `test-nodejs.php` - Read-only diagnostics
- ✅ `fix-storage.php` - Creates folders only, no destructive operations
- ✅ `index.php` - Laravel entry (protected by framework)

**After fixes complete, DELETE these files:**
```bash
rm public/test-nodejs.php
rm public/fix-storage.php
```

Or add `.htaccess` protection:
```apache
# In public/.htaccess, add:
<FilesMatch "^(test-nodejs|fix-storage)\.php$">
    Require ip YOUR_IP_ADDRESS
</FilesMatch>
```

### Files in Root Directory

**Never web-accessible:**
- ❌ `.env` - Protected by Laravel
- ❌ `*.sh` scripts - Only executable via SSH
- ❌ `server.js` - Only runs as Node process
- ❌ `composer.json`, `package.json` - Protected

---

## 📊 File Purposes

| File | Location | Access | Purpose |
|------|----------|--------|---------|
| `test-nodejs.php` | `public/` | Web + CLI | Test Node.js connection with full diagnostics |
| `fix-storage.php` | `public/` | Web | Create missing storage folders |
| `quick-fix.sh` | Root | SSH only | One-click fix both errors |
| `fix-permissions.sh` | Root | SSH only | Fix storage permissions |
| `fix-nodejs-connection.sh` | Root | SSH only | Update .env for localhost |
| `health-check.sh` | Root | SSH only | System diagnostics |

---

## 🚀 Recommended Workflow

### For Shared Hosting (No SSH)

1. Upload files via cPanel File Manager
2. Visit: `https://wablast.inilaku.com/fix-storage.php`
3. Visit: `https://wablast.inilaku.com/test-nodejs.php`
4. Manually update `.env` via cPanel File Editor:
   ```
   WA_URL_SERVER=http://localhost
   ```
5. Visit: `/migrate` to run migrations
6. Test dashboard

### For VPS/Dedicated (With SSH)

1. Upload files via Git or FTP
2. SSH into server
3. Run: `bash quick-fix.sh`
4. Everything fixed automatically ✅
5. Test dashboard

---

## 🎯 Production URLs

| URL | File | Purpose |
|-----|------|---------|
| https://wablast.inilaku.com | `public/index.php` | Main application |
| https://wablast.inilaku.com/test-nodejs.php | `public/test-nodejs.php` | Connection test |
| https://wablast.inilaku.com/fix-storage.php | `public/fix-storage.php` | Storage fix |
| https://wablast.inilaku.com/migrate | Laravel route | Run migrations |
| https://wablast.inilaku.com/clear-cache | Laravel route | Clear caches |

---

**Last Updated:** 2025-12-14  
**Server:** wablast.inilaku.com  
**Document Root:** `/home/iniz8459/public_html/public`
