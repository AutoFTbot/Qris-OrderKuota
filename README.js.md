# QRIS Payment Package

Package Node.js untuk generate QRIS, cek status pembayaran, dan generate PDF bukti transaksi (receipt) otomatis.

## Fitur

- Generate QRIS dengan nominal tertentu
- Tambah logo di tengah QR
- Cek status pembayaran
- Validasi format QRIS
- Perhitungan checksum CRC16
- Generate PDF bukti transaksi otomatis saat pembayaran sukses
  
## Contoh Output Receipt

<img src="https://raw.githubusercontent.com/AutoFTbot/Qris-OrderKuota/refs/heads/main/img/buktitrx.jpg" width="250" alt="Contoh Receipt QRIS" />

## Instalasi

```bash
npm install qris-payment
```

## Penggunaan

### Inisialisasi

```javascript
const QRISPayment = require('qris-payment');

const config = {
    storeName: 'NAMA TOKO ANDA', // Nama toko yang akan tampil di receipt
    merchantId: 'YOUR_MERCHANT_ID',
    apiKey: 'YOUR_API_KEY',
    baseQrString: 'YOUR_BASE_QR_STRING',
    logoPath: 'path/to/logo.png' // Opsional
};

const qris = new QRISPayment(config);
```

### Generate QRIS

```javascript
async function generateQR() {
    const { qrString, qrBuffer } = await qris.generateQR(10000);
    // Simpan QR ke file
    fs.writeFileSync('qr.png', qrBuffer);
    console.log('QR String:', qrString);
}
```

### Cek Status Pembayaran Realtime (Polling)

```javascript
async function waitForPayment(reference, amount, maxAttempts = 30, interval = 10000) {
    for (let attempt = 1; attempt <= maxAttempts; attempt++) {
        const result = await qris.checkPayment(reference, amount);
        if (result.success && result.data.status === 'PAID') {
            console.log('✓ Pembayaran berhasil!');
            return result;
        }
        if (attempt < maxAttempts) {
            await new Promise(resolve => setTimeout(resolve, interval));
        }
    }
    throw new Error('Pembayaran tidak diterima dalam waktu yang ditentukan.');
}

async function testRealtimePayment() {
    const amount = 10000;
    const { qrBuffer } = await qris.generateQR(amount);
    fs.writeFileSync('test_qr.png', qrBuffer);
    console.log('QR Code berhasil dibuat. Silakan scan dan lakukan pembayaran.');
    const reference = 'TEST' + Date.now();
    const paymentResult = await waitForPayment(reference, amount);
    if (paymentResult.success && paymentResult.data.status === 'PAID') {
        if (paymentResult.receipt) {
            console.log('✓ Bukti transaksi berhasil dibuat:', paymentResult.receipt.filePath);
        }
    }
}
```

### Receipt Otomatis
- Receipt PDF akan otomatis dibuat saat status pembayaran menjadi PAID.
- Path file receipt bisa diambil dari `paymentResult.receipt.filePath`.
- Tidak perlu memanggil generateReceipt manual kecuali untuk kebutuhan khusus.

## Konfigurasi

| Parameter   | Tipe   | Deskripsi                                 |
|-------------|--------|-------------------------------------------|
| storeName   | string | Nama toko yang tampil di receipt           |
| merchantId  | string | ID Merchant QRIS                          |
| apiKey      | string | API Key untuk cek pembayaran               |
| baseQrString| string | String dasar QRIS                         |
| logoPath    | string | Path ke file logo (opsional)               |

## Response

### Generate QR

```javascript
{
    qrString: "000201010212...", // String QRIS
    qrBuffer: <Buffer ...> // Buffer gambar QR
}
```

### Cek Pembayaran

```javascript
{
    success: true,
    data: {
        status: 'PAID' | 'UNPAID',
        amount: number,
        reference: string,
        date?: string, // Hanya jika status PAID
        brand_name?: string, // Hanya jika status PAID
        buyer_reff?: string // Hanya jika status PAID
    },
    receipt?: {
        success: true,
        filePath: string,
        fileName: string
    }
}
```

## Error Handling

Package ini akan melempar error dengan pesan yang jelas jika terjadi masalah:
- Format QRIS tidak valid
- Gagal generate QR
- Gagal cek status pembayaran
- API key tidak valid
- dll

## Contoh Lengkap

```javascript
const QRISPayment = require('qris-payment');
const fs = require('fs');

const config = {
    storeName: 'AGIN STORE',
    merchantId: '#',
    apiKey: '#',
    baseQrString: '#',
    logoPath: 'https://i.ibb.co/0r00000/logo-agin.png'
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

## Persyaratan Sistem

- Node.js >= 20.18.3
- Dependencies:
  - qrcode: ^1.5.0
  - canvas: ^2.9.0
  - axios: ^1.3.0
  - pdfkit: ^0.13.0
  - moment: ^2.29.4

## Lisensi

MIT

## Kontribusi

Silakan buat pull request untuk kontribusi. Untuk perubahan besar, buka issue terlebih dahulu untuk mendiskusikan perubahan yang diinginkan.

## Support

Jika menemukan masalah atau memiliki pertanyaan, silakan buka issue di repository ini. 
