# AutoFT QRIS Generator

Package Node.js untuk generate QRIS, cek status pembayaran, dan buat receipt PDF otomatis.

[![npm version](https://badge.fury.io/js/autoft-qris.svg)](https://badge.fury.io/js/autoft-qris)
[![Downloads](https://img.shields.io/npm/dw/autoft-qris.svg)](https://www.npmjs.com/package/autoft-qris)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Version 0.0.13** - Updated by AutoftBot69

## ✨ Fitur Utama

-  **2 Tema QRIS**: Biru (default) dan Hijau (meta style)
-  ️ **Logo Custom**: Tambah logo di tengah QR code
-  **Cek Pe mbayaran**: Realtime monitoring status pembayaran
-  **Receipt PDF**: Generate bukti transaksi otomatis
-  **Cek Saldo**: Monitor saldo akun API
-  **Dubal Support**: ESM dan CommonJS

## 📦 Instalasi

```bash
npm install autoft-qris
```

## Quick Start

### 1. Setup Dasar

```javascript
const { QRISGenerator, PaymentChecker, ReceiptGenerator } = require('autoft-qris');

// Konfigurasi
const config = {
    storeName: 'Toko Saya',
    auth_username: 'username_api',
    auth_token: 'token_api',
    baseQrString: 'qris_string_dari_bank',
    mutasi_url: 'https://sawargipay.net/api/mutasi', //contoh
    logoPath: './logo.png' // opsional
};
```

### 2. Generate QRIS

```javascript
// Buat QR Generator
const qrisGen = new QRISGenerator(config, 'theme1');

// Generate QR untuk nominal tertentu
const amount = 50000;
const qrString = qrisGen.generateQrString(amount);
const qrImage = await qrisGen.generateQRWithLogo(qrString);

// Simpan QR code
require('fs').writeFileSync('qris.png', qrImage);
console.log('QR Code berhasil dibuat: qris.png');
```

### 3. Cek Status Pembayaran

```javascript
// Setup payment checker
const paymentChecker = new PaymentChecker({
    auth_token: config.auth_token,
    auth_username: config.auth_username,
    mutasi_url: config.mutasi_url
});

// Cek pembayaran
const result = await paymentChecker.checkPaymentStatus('REF123', 50000);
if (result.success && result.data.status === 'PAID') {
    console.log('Pembayaran berhasil!');
    console.log('Jumlah:', result.data.amount);
    console.log('Tanggal:', result.data.date);
}
```

### 4. Generate Receipt

```javascript
// Setup receipt generator
const receiptGen = new ReceiptGenerator(config);

// Buat receipt saat pembayaran sukses
if (result.success && result.data.status === 'PAID') {
    const receipt = await receiptGen.generateReceipt(result.data);
    console.log('Receipt dibuat:', receipt.filePath);
}
```

## 🎨 Pilihan Tema

```javascript
// Tema 1 (Biru - Default)
const qrisGen1 = new QRISGenerator(config, 'theme1');

// Tema 2 (Hijau - Meta Style)
const qrisGen2 = new QRISGenerator(config, 'theme2');

// Ganti tema
qrisGen1.setTheme('theme2');

// Lihat tema yang tersedia
console.log(QRISGenerator.getAvailableThemes());
```

## 💰 Cek Saldo

```javascript
const saldo = await paymentChecker.checkSaldo();
if (saldo.success) {
    console.log('Saldo Anda:', saldo.data.saldo);
}
```

## 🔄 Contoh Lengkap - Monitoring Pembayaran

```javascript
const { QRISGenerator, PaymentChecker, ReceiptGenerator } = require('autoft-qris');
const fs = require('fs');

async function prosesTransaksi() {
    // Setup
    const config = {
        storeName: 'Toko Saya',
        auth_username: 'your_username',
        auth_token: 'your_token',
        baseQrString: 'your_qris_string',
        mutasi_url: 'https://sawargipay.net/api/mutasi'
    };

    const qrisGen = new QRISGenerator(config, 'theme1');
    const paymentChecker = new PaymentChecker(config);
    const receiptGen = new ReceiptGenerator(config);

    try {
        // 1. Buat QR Code
        const amount = 75000;
        const reference = 'TRX' + Date.now();
        
        const qrString = qrisGen.generateQrString(amount);
        const qrImage = await qrisGen.generateQRWithLogo(qrString);
        fs.writeFileSync('pembayaran.png', qrImage);
        
        console.log(`QR Code dibuat untuk Rp ${amount.toLocaleString()}`);
        console.log('Silakan scan QR code untuk pembayaran');
        
        // 2. Monitor pembayaran (5 menit)
        console.log('Menunggu pembayaran...');
        const startTime = Date.now();
        const timeout = 5 * 60 * 1000; // 5 menit
        
        while (Date.now() - startTime < timeout) {
            const result = await paymentChecker.checkPaymentStatus(reference, amount);
            
            if (result.success && result.data.status === 'PAID') {
                console.log('✅ Pembayaran berhasil!');
                
                // 3. Buat receipt
                const receipt = await receiptGen.generateReceipt(result.data);
                console.log('📄 Receipt:', receipt.filePath);
                
                return result.data;
            }
            
            // Tunggu 3 detik sebelum cek lagi
            await new Promise(resolve => setTimeout(resolve, 3000));
            console.log('⏳ Masih menunggu...');
        }
        
        console.log('❌ Timeout: Pembayaran tidak diterima dalam 5 menit');
        
    } catch (error) {
        console.error('Error:', error.message);
    }
}

// Jalankan
prosesTransaksi();
```

## ⚙️ Konfigurasi API

Untuk menggunakan package ini, Anda perlu:

1. **Username & Token API**: Kredensial untuk akses API
2. **Base QRIS String**: String QRIS dari bank Anda
3. **Mutasi URL**: Endpoint API untuk cek transaksi

```javascript
const config = {
    auth_username: 'username_dari_provider',
    auth_token: 'token_dari_provider', 
    baseQrString: 'qris_string_dari_bank',
    mutasi_url: 'https://sawargipay.net/api/mutasi'
};
```

**📞 Untuk mendapatkan kredensial API, hubungi [@AutoFtBot69](https://t.me/AutoFtBot69)**

## 📋 Requirements

- Node.js >= 20.18.3 LTS
- Canvas support (untuk generate gambar)

## 🆘 FAQ

**Q: Bagaimana cara mendapatkan base QRIS string?**  
A: Dapatkan dari bank atau payment gateway yang Anda gunakan.

**Q: Apakah bisa custom logo dan nama toko?**  
A: Ya, atur `logoPath` dan `storeName` di konfigurasi.

**Q: Berapa lama monitoring pembayaran?**  
A: Default 5 menit, bisa disesuaikan sesuai kebutuhan.

**Q: Format receipt seperti apa?**  
A: PDF dengan logo, detail transaksi, dan QR code.

## 🤝 Contributors

- **AutoFTbot** - Original author
- **AlfiDev** - ESM/CommonJS support & improvements

## 📄 License

MIT License

## 🔗 Links

- [GitHub Repository](https://github.com/AutoFTbot/Qris-OrderKuota)
- [NPM Package](https://www.npmjs.com/package/autoft-qris)
- [Support](https://t.me/AutoFtBot69)
