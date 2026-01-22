# OrderKuota Telegram Bot

Bot Telegram untuk mengakses layanan OrderKuota dengan mudah. Cek saldo QRIS, lihat mutasi transaksi, dan kelola akun OrderKuota langsung dari Telegram.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)

## ✨ Fitur

- 🔐 Login dengan username dan password
- 📧 Verifikasi OTP via email
- 💰 Cek saldo QRIS
- 📊 Lihat riwayat mutasi transaksi
- 🔒 Session management dengan timeout otomatis
- 🧹 Auto-cleanup untuk session yang kadaluarsa

## 📋 Requirements

- PHP 7.4 atau lebih tinggi
- cURL extension enabled
- JSON extension enabled
- OpenSSL extension enabled
- SSL/HTTPS aktif di server
- Akun Telegram Bot (dapatkan token dari [@BotFather](https://t.me/BotFather))
- Akun OrderKuota yang valid

## 🚀 Quick Start (cPanel)

### 1. Upload ke cPanel

1. Login cPanel → **File Manager**
2. Buat folder `bot` di `public_html atau apalah bebas`
3. Upload `bot/bot.php` ke folder `bot`
4. Upload `bot/test.php` ke folder `bot`
5. Buat file `.env` di `public_html atau apalah bebas`:

```env
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
WEBHOOK_URL=https://yourdomain.com/bot/bot.php
```

### 2. Test & Set Webhook

```
https://yourdomain.com/bot/test.php
https://yourdomain.com/bot/bot.php?setwebhook
```

### 3. Test di Telegram

Kirim `/start` ke bot Anda!

## 🎯 Cara Penggunaan

### Commands yang Tersedia

| Command | Deskripsi |
|---------|-----------|
| `/start` | Mulai bot dan lihat menu utama |
| `/help` | Bantuan penggunaan bot |
| `/login username password` | Login ke akun OrderKuota |
| `/otp kode` | Verifikasi OTP jika diminta |
| `/saldo` | Cek saldo QRIS |
| `/mutasi` | Lihat 5 transaksi terakhir |
| `/logout` | Logout dari sesi |
| `/donate` | Dukung pengembangan bot |

### Contoh Penggunaan

1. **Login:**
```
/login user@email.com password123
```

2. **Jika diminta OTP:**
```
/otp 123456
```

3. **Cek Saldo:**
```
/saldo
```

4. **Lihat Mutasi:**
```
/mutasi
```

## 🔐 Keamanan

Bot ini menggunakan security best practices:

✅ **Fitur Keamanan:**
- SSL/HTTPS required untuk webhook
- Input sanitization untuk mencegah XSS
- Session timeout otomatis (1 jam)
- Cookie dan session dibersihkan saat logout
- Environment variables untuk kredensial
- Error logging untuk monitoring


## 🔧 Konfigurasi

### Environment Variables (.env)

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_bot_token_here
WEBHOOK_URL=https://yourdomain.com/bot/bot.php
```

**Catatan:** File `.env` harus di root directory, bukan di folder `bot/`

### Session Storage

Sessions disimpan di `/tmp/telegram_sessions.json` dengan auto-cleanup setelah 1 jam.

### Cookie Storage

Cookies disimpan per user di `/tmp/orderkuota_cookies_{chatId}.txt`

## 🛡️ Keamanan

- ⚠️ **PENTING**: Jangan commit file `.env` ke repository
- Token bot dan kredensial disimpan sebagai environment variables
- Session timeout otomatis setelah 1 jam
- Cookie dan session dibersihkan saat logout
- Input user di-sanitize untuk mencegah XSS
- SSL/HTTPS required untuk webhook

## 📝 Development

### Error Logging

Error log disimpan di `/tmp/telegram_bot_errors.log`. Periksa file ini untuk debugging.

## 💝 Donate

Jika bot ini bermanfaat, dukung pengembangan dengan donasi:

[QRIS Donate](https://github.com/AutoFTbot/AutoFTbot/blob/main/qris.png)

## 📞 Contact

- Telegram: [@AutoFtBot69](https://t.me/AutoFtBot69)

## ⚠️ Disclaimer

Bot ini dibuat untuk tujuan edukasi. Gunakan dengan bijak dan patuhi terms of service OrderKuota.

## 🎉 Features Highlight

- ✅ **No External Dependencies** - Tidak perlu library tambahan
- ✅ **cPanel Ready** - Mudah deploy di shared hosting
- ✅ **Auto Session Management** - Session otomatis dibersihkan
- ✅ **Secure** - SSL/HTTPS, input sanitization, session timeout
- ✅ **Easy to Use** - Setup dalam 5 menit
- ✅ **Well Documented** - Dokumentasi lengkap dan jelas

---

Made with ❤️ for OrderKuota users
