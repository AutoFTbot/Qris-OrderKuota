## 🎯 Overview
Panduan lengkap untuk menginstall dan mengkonfigurasi API Mutasi QRIS (OrderKuota) di hosting cPanel.

**Pakai hosting ini aja biar lancar soalnya udah saya pakai 1 tahun aman: cPanel full license, cron job unlimited, server Indo cepat!**  
🚀 **[Hosting Mantap - Diskon Spesial + Free Migrate](https://clients.anymhost.id/aff.php?aff=833)**

---

## 📦 Files yang Dibutuhkan

### ✅ File Utama (WAJIB)
- `mutasi.php` - API utama
- `.htaccess` - Konfigurasi web server

---

## 🚀 Langkah Pemasangan

### Step 1: Login ke cPanel
1. Buka browser dan akses cPanel Anda
2. Login dengan username dan password hosting
3. Cari dan klik **"File Manager"**

### Step 2: Navigasi ke Folder Website
1. Di File Manager, buka folder **`public_html`**
2. Jika menggunakan subdomain, buka folder subdomain yang sesuai
3. Pastikan Anda berada di root directory website

### Step 3: Upload Files
1. Klik tombol **"Upload"** di File Manager
2. Upload file-file berikut:
   ```
   mutasi.php
   .htaccess
   ping.php (opsional)
   ```
3. Tunggu hingga upload selesai

### Step 4: Set Permissions (PENTING!)
1. Klik kanan pada `mutasi.php`
2. Pilih **"Change Permissions"**
3. Set permission ke **644** (rw-r--r--)
4. Klik **"Change Permissions"**
---

## ⚙️ Konfigurasi Server

### PHP Requirements
- **PHP Version**: 7.4 atau lebih tinggi
- **Extensions Required**:
  - `json` (biasanya sudah aktif)
  - `zlib` (untuk gzuncompress)
  - `mbstring` (untuk string handling)

### Cek PHP Version
1. Di cPanel, cari **"Select PHP Version"**
2. Pastikan menggunakan PHP 7.4+ atau 8.x
3. Aktifkan extensions yang dibutuhkan

### .htaccess Configuration
File `.htaccess` sudah dikonfigurasi untuk:
- Redirect HTTP ke HTTPS
- Security headers
- Block akses ke file sensitif
- Optimasi performance

---

## 🧪 Testing Installation

### Test 1: Ping Server
1. Buka browser dan akses: `https://yourdomain.com/ping.php`
2. Harus menampilkan: `{"status":"ok","message":"Server is running"}`


### Test 3: API Endpoint
```bash
curl -X POST https://yourdomain.com/api/mutasi \
-H "Content-Type: application/json" \
-d '{"auth_token":"test","auth_username":"test"}'
```

---


## 🔒 Security Checklist

### ✅ File Permissions
- `mutasi.php`: **644**
- `.htaccess`: **644**
- Folder: **755**

### ✅ HTTPS Configuration
- Pastikan SSL certificate aktif
- Force redirect HTTP ke HTTPS
- Gunakan HTTPS untuk semua API calls
