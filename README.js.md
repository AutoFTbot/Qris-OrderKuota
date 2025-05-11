# QRIS Payment Package

Package Node.js untuk generate QRIS, cek status pembayaran, dan generate PDF bukti transaksi (receipt) otomatis.

## Fitur

- Generate QRIS dengan nominal tertentu
- Tambah logo di tengah QR
- Cek status pembayaran
- Validasi format QRIS
- Perhitungan checksum CRC16
- Generate PDF bukti transaksi otomatis saat pembayaran sukses

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
    storeName: 'NAMA TOKO ANDA',
    merchantId: 'YOUR_MERCHANT_ID',
    apiKey: 'YOUR_API_KEY',
    baseQrString: 'YOUR_BASE_QR_STRING',
    logoPath: 'path/to/logo.png'
};

const qris = new QRISPayment(config);

async function main() {
    const amount = 10000;
    const { qrBuffer } = await qris.generateQR(amount);
    fs.writeFileSync('qr.png', qrBuffer);
    const reference = 'REF' + Date.now();
    const paymentResult = await waitForPayment(reference, amount);
    if (paymentResult.success && paymentResult.data.status === 'PAID') {
        if (paymentResult.receipt) {
            console.log('✓ Bukti transaksi berhasil dibuat:', paymentResult.receipt.filePath);
        }
    }
}

main();
```

## Persyaratan Sistem

- Node.js >= 12.0.0
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
