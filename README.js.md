# AutoFT QRIS Generator

Node.js package untuk generate QRIS, cek status pembayaran, dan otomatis generate PDF receipt menggunakan API OrderKuota.

[![npm version](https://badge.fury.io/js/autoft-qris.svg)](https://badge.fury.io/js/autoft-qris)
[![Downloads](https://img.shields.io/npm/dw/autoft-qris.svg)](https://www.npmjs.com/package/autoft-qris)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Updated by AlfiDev as contributor - Version 0.0.9**

## 🚀 Pembaruan Terbaru v0.0.9

- ✨ **Dual Module Support**: ESM dan CommonJS
- 🔧 **Clean Code**: Code tanpa komentar dan lebih bersih
- 🎨 **2 Tema QRIS**: Default (biru) dan Meta Style (hijau)
- 🏷️ **Logo Support**: Tambah logo di tengah QR code
- 💰 **Payment Checker**: Realtime status pembayaran
- 🧾 **Receipt Generator**: PDF receipt otomatis
- 💳 **Balance Checker**: Cek saldo akun API
- 🔒 **CRC16 Validation**: Checksum validation
- 📱 **QRIS Indonesia**: Format standar Indonesia

## Fitur

- Generate QRIS dengan nominal tertentu
- **2 Tema QRIS**: Tema (default) dan Tema style Meta
- Tambah logo di tengah QR
- Cek status pembayaran (realtime polling) menggunakan API OrderKuota
- Cek saldo API akun (endpoint saldo)
- Generate PDF bukti transaksi otomatis saat pembayaran sukses
- Support ESM dan CommonJS module systems
- Node.js version compatibility checking

## Contoh Output Receipt

<img src="https://raw.githubusercontent.com/AutoFTbot/Qris-OrderKuota/refs/heads/main/img/buktitrx.jpg" width="250" alt="Contoh Receipt QRIS" />

## Instalasi

```bash
npm install autoft-qris
```

## Penggunaan

### ESM (ES Modules)

```javascript
import { QRISGenerator, PaymentChecker, ReceiptGenerator } from 'autoft-qris';
import { writeFileSync } from 'fs';

const config = {
    storeName: 'Nama Toko Contoh',
    auth_username: '#',
    auth_token: '#',
    baseQrString: '#',
    logoPath: './logo-agin.png'
};

const qrisGen = new QRISGenerator(config, 'theme1');
const paymentChecker = new PaymentChecker({
    auth_token: config.auth_token,
    auth_username: config.auth_username
});
const receiptGen = new ReceiptGenerator(config);

const amount = 50000;
const qrString = qrisGen.generateQrString(amount);
const qrBuffer = await qrisGen.generateQRWithLogo(qrString);
writeFileSync('qris.png', qrBuffer);

const paymentResult = await paymentChecker.checkPaymentStatus('REF123', amount);
if (paymentResult.success && paymentResult.data.status === 'PAID') {
    const receipt = await receiptGen.generateReceipt(paymentResult.data);
    console.log('Receipt generated:', receipt.filePath);
}
```

### CommonJS

```javascript
const { QRISGenerator, PaymentChecker, ReceiptGenerator } = require('autoft-qris');
const fs = require('fs');

const config = {
    storeName: 'Nama Toko Contoh',
    auth_username: '#',
    auth_token: '#',
    baseQrString: '#',
    logoPath: './logo-agin.png'
};

const qrisGen = new QRISGenerator(config, 'theme1');
const paymentChecker = new PaymentChecker({
    auth_token: config.auth_token,
    auth_username: config.auth_username
});
const receiptGen = new ReceiptGenerator(config);

async function main() {
    const amount = 50000;
    const qrString = qrisGen.generateQrString(amount);
    const qrBuffer = await qrisGen.generateQRWithLogo(qrString);
    fs.writeFileSync('qris.png', qrBuffer);

    const paymentResult = await paymentChecker.checkPaymentStatus('REF123', amount);
    if (paymentResult.success && paymentResult.data.status === 'PAID') {
        const receipt = await receiptGen.generateReceipt(paymentResult.data);
        console.log('Receipt generated:', receipt.filePath);
    }
}

main();
```

## Penggunaan dengan Tema

### Tema yang Tersedia

1. **Theme 1 (Default)**: QRIS dengan aksen warna biru
2. **Theme 2 (Meta Style)**: QRIS dengan aksen warna meta

### Contoh Penggunaan Tema

```javascript
const { QRISGenerator } = require('autoft-qris');
const fs = require('fs');

const config = {
    storeName: 'Nama Toko Contoh',
    auth_username: '#',
    auth_token: '#',
    baseQrString: '#',
    logoPath: './logo-agin.png'
};

const qrGenerator1 = new QRISGenerator(config, 'theme1');
const qrString1 = qrGenerator1.generateQrString(50000);
const qrBuffer1 = await qrGenerator1.generateQRWithLogo(qrString1);
fs.writeFileSync('qris-theme1.png', qrBuffer1);

const qrGenerator2 = new QRISGenerator(config, 'theme2');
const qrString2 = qrGenerator2.generateQrString(50000);
const qrBuffer2 = await qrGenerator2.generateQRWithLogo(qrString2);
fs.writeFileSync('qris-theme2.png', qrBuffer2);

const qrGenerator = new QRISGenerator(config, 'theme1');
qrGenerator.setTheme('theme2');

const themes = QRISGenerator.getAvailableThemes();
console.log(themes);
```

## Penggunaan Complete Example

```javascript
const { QRISGenerator, PaymentChecker, ReceiptGenerator } = require('autoft-qris');
const fs = require('fs');

const config = {
    storeName: 'Nama Toko Contoh',
    auth_username: '#',
    auth_token: '#',
    baseQrString: '#',
    logoPath: './logo-agin.png'
};

const qrisGen = new QRISGenerator(config, 'theme1');
const paymentChecker = new PaymentChecker({
    auth_token: config.auth_token,
    auth_username: config.auth_username
});
const receiptGen = new ReceiptGenerator(config);

async function main() {
    try {
        console.log('=== TEST REALTIME QRIS PAYMENT ===\n');
        const randomAmount = Math.floor(Math.random() * 99) + 1;
        const amount = 100 + randomAmount;
        const reference = 'REF' + Date.now();
        
        const qrString = qrisGen.generateQrString(amount);
        const qrBuffer = await qrisGen.generateQRWithLogo(qrString);
        fs.writeFileSync('qr.png', qrBuffer);
        
        console.log('=== TRANSACTION DETAILS ===');
        console.log('Reference:', reference);
        console.log('Amount:', amount);
        console.log('QR Image:', 'qr.png');
        console.log('\nSilakan scan QR code dan lakukan pembayaran');
        console.log('\nMenunggu pembayaran...\n');
        
        const startTime = Date.now();
        const timeout = 5 * 60 * 1000;
        
        while (Date.now() - startTime < timeout) {
            const result = await paymentChecker.checkPaymentStatus(reference, amount);
            if (result.success && result.data.status === 'PAID') {
                console.log('✓ Pembayaran berhasil!');
                
                const receipt = await receiptGen.generateReceipt(result.data);
                console.log('✓ Bukti transaksi:', receipt.filePath);
                return;
            }
            await new Promise(resolve => setTimeout(resolve, 3000));
            console.log('Menunggu pembayaran...');
        }
        throw new Error('Timeout: Pembayaran tidak diterima dalam 5 menit');
    } catch (error) {
        console.error('Error:', error.message);
    }
}

main();
```

## API Reference

### QRISGenerator

```javascript
const qrisGen = new QRISGenerator(config, theme)

qrisGen.generateQrString(amount)
await qrisGen.generateQRWithLogo(qrString)
qrisGen.calculateCRC16(str)
qrisGen.setTheme(theme)
qrisGen.getCurrentTheme()
QRISGenerator.getAvailableThemes()
```

### PaymentChecker

```javascript
const paymentChecker = new PaymentChecker(config)

await paymentChecker.checkPaymentStatus(reference, amount)
await paymentChecker.checkSaldo()
```

### ReceiptGenerator

```javascript
const receiptGen = new ReceiptGenerator(config)

await receiptGen.generateReceipt(transactionData)
```

## Cek Saldo

```javascript
const paymentChecker = new PaymentChecker({
    auth_token: 'your_token',
    auth_username: 'your_username'
});

const saldo = await paymentChecker.checkSaldo();
if (saldo.success) {
    console.log('Saldo:', saldo.data.saldo);
}
```

## Module Support

Package ini mendukung both ESM dan CommonJS:

- **ESM**: Menggunakan `.mjs` files dan `import` statements
- **CommonJS**: Menggunakan `.cjs` files dan `require()` statements
- **Auto-detect**: Package otomatis mendeteksi module system yang digunakan

## Node.js Version Compatibility

Package ini akan menampilkan warning jika menggunakan Node.js 21+ karena masalah kompatibilitas dengan chalk package. Recommended menggunakan Node.js 20.18.3 LTS.

## Konfigurasi API

Package ini menggunakan API OrderKuota untuk cek status pembayaran. Pastikan Anda memiliki:

- `auth_username`: Username autentikasi
- `auth_token`: Token autentikasi

**Untuk mendapatkan kredensial API, hubungi [@AutoFtBot69](https://t.me/AutoFtBot69)**

## FAQ

**Q: Apakah receipt bisa custom logo dan nama toko?**  
A: Bisa, cukup atur `logoPath` dan `storeName` di config.

**Q: Apakah receipt otomatis dibuat saat pembayaran sukses?**  
A: Ya, receipt PDF otomatis dibuat dan path-nya bisa diambil dari `paymentResult.receipt.filePath`.

**Q: Apakah bisa polling pembayaran lebih cepat/lebih lama?**  
A: Bisa, atur parameter `interval` dan `maxAttempts` pada fungsi polling.

**Q: Bagaimana cara mendapatkan kredensial API OrderKuota?**  
A: Hubungi [@AutoFtBot69](https://t.me/AutoFtBot69) untuk mendapatkan username dan token autentikasi.

**Q: Apakah support ESM dan CommonJS?**  
A: Ya, package ini fully support both module systems dengan auto-detection.

## Requirements

- Node.js >= 20.18.3 LTS (recommended)
- Canvas support untuk image generation

## Contributors

- **AutoFTbot** - Original author & maintainer
- **AlfiDev** - ESM/CommonJS dual support contributor
  - GitHub: [github.com/cloudkuimages](https://github.com/cloudkuimages)
  - Telegram: [@cloudkudev](https://t.me/cloudkudev)

## Kontribusi

Pull request sangat diterima!  
Buka issue untuk diskusi fitur/bug sebelum submit PR.

## Support

Jika ada pertanyaan, silakan buka [issue di GitHub](https://github.com/AutoFTbot/Qris-OrderKuota/issues)

## Repository

[GitHub Repository](https://github.com/AutoFTbot/Qris-OrderKuota)

## License

MIT # AutoFT QRIS Generator

Package untuk generate QRIS dengan 2 tema (Biru & Hijau) dan cek payment status secara realtime dengan API OrderKuota.

**Updated by AlfiDev as contributor - Version 0.0.7**

## Features

- ✨ Dual module support (ESM & CommonJS)
- 🎨 2 tema QR code yang dapat disesuaikan
- 🏷️ Logo support pada QR code
- 🔒 CRC16 checksum validation
- 📱 QRIS format support untuk Indonesia

## Installation

```bash
npm install autoft-qris
```

## Usage

### ESM (ES Modules)

```javascript
import { QRISGenerator } from 'autoft-qris';

const config = {
    baseQrString: 'your_base_qris_string_here',
    logoPath: './path/to/logo.png' // optional
};

const qrisGen = new QRISGenerator(config, 'theme1');

const amount = 50000;
const qrString = qrisGen.generateQrString(amount);
const qrImage = await qrisGen.generateQRWithLogo(qrString);

console.log('QR String:', qrString);
```

### CommonJS

```javascript
const { QRISGenerator } = require('autoft-qris');

const config = {
    baseQrString: 'your_base_qris_string_here',
    logoPath: './path/to/logo.png' // optional
};

const qrisGen = new QRISGenerator(config, 'theme2');

const amount = 50000;
const qrString = qrisGen.generateQrString(amount);
const qrImage = await qrisGen.generateQRWithLogo(qrString);

console.log('QR String:', qrString);
```

### Available Themes

```javascript
// Get available themes
const themes = QRISGenerator.getAvailableThemes();
console.log(themes);

// Switch theme
qrisGen.setTheme('theme2');
console.log('Current theme:', qrisGen.getCurrentTheme());
```

### Individual Theme Classes

#### ESM Import

```javascript
import QRISGeneratorTheme1 from 'autoft-qris/src/qr-generator.mjs';
import QRISGeneratorTheme2 from 'autoft-qris/src/qr-generator2.mjs';
```

#### CommonJS Require

```javascript
const QRISGeneratorTheme1 = require('autoft-qris/src/qr-generator.cjs');
const QRISGeneratorTheme2 = require('autoft-qris/src/qr-generator2.cjs');
```

## API Reference

### Constructor

```javascript
new QRISGenerator(config, theme)
```

- `config` (Object): Configuration object
  - `baseQrString` (String): Base QRIS string (required)
  - `logoPath` (String): Path to logo image (optional)
- `theme` (String): Theme selection ('theme1' or 'theme2', default: 'theme1')

### Methods

#### `generateQrString(amount)`
Generate QRIS string with specified amount.

**Parameters:**
- `amount` (Number): Payment amount

**Returns:** String - Complete QRIS string with checksum

#### `generateQRWithLogo(qrString)`
Generate QR code image with optional logo.

**Parameters:**
- `qrString` (String): QRIS string to encode

**Returns:** Promise<Buffer> - PNG image buffer

#### `calculateCRC16(str)`
Calculate CRC16 checksum for QRIS string.

**Parameters:**
- `str` (String): String to calculate checksum for

**Returns:** String - 4-character uppercase hex checksum

#### `setTheme(theme)`
Switch between themes.

**Parameters:**
- `theme` (String): 'theme1' or 'theme2'

#### `getCurrentTheme()`
Get current active theme.

**Returns:** String - Current theme name

#### `static getAvailableThemes()`
Get list of available themes.

**Returns:** Array - Available themes with descriptions

## Module Support

This package supports both ESM and CommonJS:

- **ESM**: Uses `.mjs` files and `import` statements
- **CommonJS**: Uses `.cjs` files and `require()` statements
- **Automatic**: Package automatically detects your module system

## Requirements

- Node.js >= 20.18.3
- Canvas support for image generation

## License

MIT

## Contributors

- **AutoFTbot** - Original author
- **AlfiDev** - ESM/CommonJS dual support contributor

## Repository

[GitHub Repository](https://github.com/AutoFTbot/Qris-OrderKuota)
