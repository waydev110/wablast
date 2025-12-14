# 🚀 Setup Node.js di Subdomain (Shared Hosting)

## Mengapa Pisah Subdomain?

Laravel dan Node.js **tidak bisa** di root domain yang sama karena:
- Laravel: `wablast.inilaku.com` → entry point `public/index.php`
- Node.js: `wablast.inilaku.com` → entry point `server.js`
- **KONFLIK!** ❌

**Solusi:** Node.js di subdomain terpisah:
- Laravel: `https://wablast.inilaku.com` ✅
- Node.js: `https://node.wablast.inilaku.com` ✅

---

## 📋 Step-by-Step Setup

### Step 1: Buat Subdomain di cPanel

1. **Login cPanel → Domains → Subdomains**

2. **Buat subdomain baru:**
   ```
   Subdomain: node
   Domain: wablast.inilaku.com
   Document Root: /home/iniz8459/node_app
   ```
   
3. **Klik "Create"**

Subdomain akan aktif: `https://node.wablast.inilaku.com`

---

### Step 2: Setup Node.js Application

1. **cPanel → Software → Setup Node.js App**

2. **Klik "CREATE APPLICATION"**

3. **Isi form:**
   ```
   Node.js version: 16.20.2 atau yang terbaru
   Application mode: Production
   Application root: /home/iniz8459/node_app
   Application URL: node.wablast.inilaku.com  ⭐ PENTING!
   Application startup file: server.js
   ```

4. **Environment variables** (klik "Add Variable"):
   ```
   DB_HOST=localhost
   DB_USER=iniz8459_wablast
   DB_PASSWORD=wablast2025
   DB_NAME=iniz8459_wablast
   PORT_NODE=3100
   ```

5. **Klik "CREATE"**

---

### Step 3: Upload Files Node.js

Upload file-file ini ke `/home/iniz8459/node_app/`:

```
/home/iniz8459/node_app/
├── server.js
├── package.json
├── package-lock.json
├── server/
│   ├── whatsapp.js
│   ├── polling-helper.js
│   ├── database/
│   │   └── index.js
│   ├── controllers/
│   ├── lib/
│   └── router/
└── credentials/
```

**Via cPanel File Manager:**
1. Navigate to `/home/iniz8459/node_app/`
2. Upload ZIP file
3. Extract

**Via FTP:**
1. Connect to FTP
2. Navigate to `/home/iniz8459/node_app/`
3. Upload semua file

---

### Step 4: Install Dependencies

Di cPanel Node.js App interface:

1. Klik **"Run NPM Install"** button
2. Atau via SSH:
   ```bash
   cd /home/iniz8459/node_app
   npm install --production
   ```

---

### Step 5: Start Application

Di cPanel Node.js App interface:

1. Klik **"START APP"** (tombol hijau)
2. Status akan berubah jadi **"Running"**
3. Buka: `https://node.wablast.inilaku.com` → harus respon (tidak error)

---

### Step 6: Update Laravel .env

**Via Web (Paling Mudah):**

1. Upload `fix-env.php` ke `public/` di server Laravel
2. Buka: `https://wablast.inilaku.com/fix-env.php`
3. Isi form dengan: `https://node.wablast.inilaku.com`
4. Klik "Update .env Configuration"

**Via Manual (SSH atau File Manager):**

Edit file `.env` di root Laravel (`/home/iniz8459/public_html/`):

```env
WA_URL_SERVER=https://node.wablast.inilaku.com
PORT_NODE=    # kosongkan, tidak perlu!
```

Clear cache:
```bash
php artisan config:clear
php artisan config:cache
```

Atau via browser: `https://wablast.inilaku.com/clear-cache`

---

### Step 7: Test Connection

**Via Web:**
```
https://wablast.inilaku.com/test-nodejs.php
```

**Expected Output:**
```
Configuration:
  WA_URL_SERVER: https://node.wablast.inilaku.com
  PORT_NODE: (not used - subdomain mode)
  Full URL: https://node.wablast.inilaku.com
  Setup Type: External/Subdomain

✓ Connection SUCCESS!
✓ API Test SUCCESS!
```

---

## 🔍 Troubleshooting

### Issue: Node.js App Won't Start

**Check logs:**
```bash
cd /home/iniz8459/node_app
cat logs/passenger.log
```

**Common fixes:**
1. Install dependencies: Click "Run NPM Install"
2. Check `server.js` exists
3. Check environment variables set correctly
4. Restart app: Click "RESTART"

---

### Issue: 502 Bad Gateway

**Causes:**
- Node.js app crashed
- Port conflict
- Missing dependencies

**Fix:**
1. cPanel → Node.js App → Check status
2. If stopped, click "START APP"
3. Check logs for errors
4. Verify environment variables

---

### Issue: Cannot Connect from Laravel

**Test manually:**
```bash
curl https://node.wablast.inilaku.com
```

Should return response (not timeout/error).

**Fix Laravel .env:**
```env
WA_URL_SERVER=https://node.wablast.inilaku.com
```

Clear cache: `/clear-cache`

---

## 🎯 Final Architecture

```
┌─────────────────────────────────────┐
│  Browser User                       │
└────────────┬────────────────────────┘
             │
             ↓
┌────────────────────────────────────┐
│  https://wablast.inilaku.com       │
│  Laravel PHP Application           │
│  (Main website, dashboard, login)  │
└────────────┬───────────────────────┘
             │ HTTP Request
             │ (Internal/External)
             ↓
┌────────────────────────────────────┐
│  https://node.wablast.inilaku.com  │
│  Node.js WhatsApp Server           │
│  (Message handling, QR codes)      │
└────────────────────────────────────┘
```

---

## 📊 Comparison: Subdomain vs Localhost

| Aspect | Subdomain | Localhost |
|--------|-----------|-----------|
| **URL** | https://node.wablast.inilaku.com | http://localhost:3100 |
| **SSL** | ✅ Auto (cPanel) | ❌ Not applicable |
| **Access** | Public (can test directly) | Internal only |
| **Setup** | cPanel Node.js Selector | Manual PM2 |
| **cPanel Support** | ✅ Yes | ❌ No |
| **Restart** | Via cPanel button | Via SSH (`pm2 restart`) |
| **Logs** | cPanel interface | `~/.pm2/logs/` |
| **Recommended** | ✅ Shared hosting | For VPS/Dedicated |

---

## ✅ Verification Checklist

- [ ] Subdomain created: `node.wablast.inilaku.com`
- [ ] Node.js app configured in cPanel
- [ ] All files uploaded to `/home/iniz8459/node_app/`
- [ ] Dependencies installed (`npm install`)
- [ ] Environment variables set
- [ ] App started (green "Running" status)
- [ ] Test subdomain: `https://node.wablast.inilaku.com` responds
- [ ] Laravel `.env` updated with subdomain URL
- [ ] Laravel cache cleared
- [ ] Connection test passed: `/test-nodejs.php` all green
- [ ] Dashboard login works
- [ ] QR code scanning works

---

## 🔐 Security Notes

### Environment Variables di cPanel

Jangan hardcode credentials di `server.js`! Gunakan environment variables di cPanel Node.js setup:

```
DB_HOST=localhost
DB_USER=iniz8459_wablast
DB_PASSWORD=wablast2025
DB_NAME=iniz8459_wablast
```

Access in code:
```javascript
const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
};
```

### SSL Certificate

cPanel Auto-SSL akan otomatis generate certificate untuk subdomain.

Check: `https://node.wablast.inilaku.com` harus punya padlock 🔒

---

## 🆘 Support

**If stuck:**

1. Check cPanel logs:
   - Node.js App interface → "View Logs"
   - Error Log (cPanel main page)

2. Test connection:
   ```bash
   curl -I https://node.wablast.inilaku.com
   ```

3. Check Laravel logs:
   ```
   storage/logs/laravel.log
   ```

4. Run diagnostic:
   ```
   https://wablast.inilaku.com/test-nodejs.php
   ```

---

**Document Version:** 1.0  
**Last Updated:** 2025-12-14  
**For:** wablast.inilaku.com shared hosting setup
