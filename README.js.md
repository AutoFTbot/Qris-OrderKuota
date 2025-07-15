# qris-payment

Node.js package untuk generate QRIS, cek status pembayaran, dan otomatis generate PDF receipt menggunakan API OrderKuota.

[![npm version](https://badge.fury.io/js/qris-payment.svg)](https://badge.fury.io/js/qris-payment)
[![Downloads](https://img.shields.io/npm/dw/qris-payment.svg)](https://www.npmjs.com/package/qris-payment)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Fitur

- Generate QRIS dengan nominal tertentu
- Tambah logo di tengah QR
- Cek status pembayaran (realtime polling) menggunakan API OrderKuota
- Generate PDF bukti transaksi otomatis saat pembayaran sukses

## Contoh Output Receipt

<img src="https://raw.githubusercontent.com/AutoFTbot/Qris-OrderKuota/refs/heads/main/img/buktitrx.jpg" width="250" alt="Contoh Receipt QRIS" />

## Instalasi

```bash
npm install autoft-qris
```

## Penggunaan Singkat

```javascript
const QRISPayment = require('autoft-qriss');
const fs = require('fs');

const config = {
    storeName: '#', //Nama Store Kalian
    auth_username: '#', //Username OrderKuota
    auth_token: '#', //Token OrderKuota
    baseQrString: '#', //StringQris
    logoPath: './logo-agin.png' //Opsional
};

const qris = new QRISPayment(config);

async function main() {
    try {
        console.log('=== TEST REALTIME QRIS PAYMENT ===\n');
        const randomAmount = Math.floor(Math.random() * 99) + 1; // Random 1-99
        const amount = 100 + randomAmount; // Base 100 + random amount
        const reference = 'REF' + Date.now();
        
        // Generate QR code
        const { qrBuffer } = await qris.generateQR(amount);
        
        // Save QR code image
        fs.writeFileSync('qr.png', qrBuffer);
        
        console.log('=== TRANSACTION DETAILS ===');
        console.log('Reference:', reference);
        console.log('Amount:', amount);
        console.log('QR Image:', 'qr.png');
        console.log('\nSilakan scan QR code dan lakukan pembayaran');
        console.log('\nMenunggu pembayaran...\n');

        // Check payment status with 5 minutes timeout
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
