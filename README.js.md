# qris-payment

Node.js package untuk generate QRIS, cek status pembayaran, dan otomatis generate PDF receipt menggunakan API OrderKuota.

[![npm version](https://badge.fury.io/js/autoft-qris.svg)](https://badge.fury.io/js/autoft-qris)
[![Downloads](https://img.shields.io/npm/dw/autoft-qris.svg)](https://www.npmjs.com/package/autoft-qris)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Fitur

- Generate QRIS dengan nominal tertentu
- **2 Tema QRIS**: Tema (default) dan Tema style Meta
- Tambah logo di tengah QR
- Cek status pembayaran (realtime polling) menggunakan API OrderKuota
- Cek saldo API akun (endpoint saldo)
- Generate PDF bukti transaksi otomatis saat pembayaran sukses

## Contoh Output Receipt

<img src="https://raw.githubusercontent.com/AutoFTbot/Qris-OrderKuota/refs/heads/main/img/buktitrx.jpg" width="250" alt="Contoh Receipt QRIS" />

## Instalasi

```bash
npm install autoft-qris
```

## Penggunaan dengan Tema

### Tema yang Tersedia

1. **Tema 1 (Default)**: QRIS dengan aksen warna biru
2. **Tema 2 (Meta)**: QRIS dengan aksen warna hijau

### Contoh Penggunaan Tema

```javascript
const { QRISGenerator } = require('autoft-qris');
const fs = require('fs');

// Konfigurasi
const config = {
    storeName: 'Nama Toko Contoh', // Nama toko
    auth_username: '#', // Username OrderKuota
    auth_token: '#', // Token OrderKuota
    baseQrString: '#', // String QRIS statis
    logoPath: './logo-agin.png' // Opsional, path logo
};

// Tema 1
const qrGenerator1 = new QRISGenerator(config, 'theme1');
const qrString1 = qrGenerator1.generateQrString(50000);
const qrBuffer1 = await qrGenerator1.generateQRWithLogo(qrString1);
fs.writeFileSync('qris-theme1.png', qrBuffer1);

// Tema 2
const qrGenerator2 = new QRISGenerator(config, 'theme2');
const qrString2 = qrGenerator2.generateQrString(50000);
const qrBuffer2 = await qrGenerator2.generateQRWithLogo(qrString2);
fs.writeFileSync('qris-theme2.png', qrBuffer2);

// Ganti tema dinamis
const qrGenerator = new QRISGenerator(config, 'theme1');
qrGenerator.setTheme('theme2'); // Ganti ke tema hijau

// Lihat tema yang tersedia
const themes = QRISGenerator.getAvailableThemes();
console.log(themes);
```

## Penggunaan Singkat (Legacy)

```javascript
const QRISPayment = require('autoft-qris');
const fs = require('fs');

const config = {
    storeName: 'Nama Toko Contoh', // Nama toko
    auth_username: '#', // Username OrderKuota
    auth_token: '#', // Token OrderKuota
    baseQrString: '#', // String QRIS statis
    logoPath: './logo-agin.png' // Opsional, path logo
};

const qris = new QRISPayment(config);

async function main() {
    try {
        console.log('=== TEST REALTIME QRIS PAYMENT ===\n');
        const randomAmount = Math.floor(Math.random() * 99) + 1; // Random 1-99
        const amount = 100 + randomAmount; // Base 100 + random amount
        const reference = 'REF' + Date.now();
        const { qrBuffer } = await qris.generateQR(amount);
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
            const result = await qris.checkPayment(reference, amount);
            if (result.success && result.data.status === 'PAID') {
                console.log('✓ Pembayaran berhasil!');
                if (result.receipt) {
                    console.log('✓ Bukti transaksi:', result.receipt.filePath);
                }
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

## Cek Saldo

Anda dapat mengecek saldo akun API yang sama dengan kredensial `auth_username` dan `auth_token` yang sudah dipakai.

Contoh lewat curl:

```bash
curl -X POST "https://orkut.ftvpn.me/api/saldo" \
   -H "Content-Type: application/json" \
   -d '{
     "auth_username": "demo_user",
     "auth_token": "demo_token_123"
   }'
```

Contoh lewat kode:

```javascript
const qris = new QRISPayment(config);
const saldo = await qris.checkSaldo();
if (saldo.success) {
  console.log('Saldo:', saldo.data.saldo);
}
```

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

## Kontribusi

Pull request sangat diterima!  
Buka issue untuk diskusi fitur/bug sebelum submit PR.

## Support

Jika ada pertanyaan, silakan buka [issue di GitHub](https://github.com/AutoFTbot/Qris-OrderKuota/issues)

## License

MIT
