# QRIS Payment Package

Package Node.js untuk generate QRIS dan cek status pembayaran.

## Fitur

- Generate QRIS dengan nominal tertentu
- Tambah logo di tengah QR
- Cek status pembayaran
- Validasi format QRIS
- Perhitungan checksum CRC16

## Instalasi

```bash
npm install qris-payment
```

## Penggunaan

### Inisialisasi

```javascript
const QRISPayment = require('qris-payment');

const config = {
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
    try {
        const { qrString, qrBuffer } = await qris.generateQR(10000);
        
        // Simpan QR ke file
        fs.writeFileSync('qr.png', qrBuffer);
        console.log('QR String:', qrString);
    } catch (error) {
        console.error(error);
    }
}
```

### Cek Status Pembayaran

```javascript
async function checkPayment() {
    try {
        const result = await qris.checkPayment('REF123', 10000);
        console.log('Status pembayaran:', result);
    } catch (error) {
        console.error(error);
    }
}
```

## Konfigurasi

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| merchantId | string | ID Merchant QRIS |
| apiKey | string | API Key untuk cek pembayaran |
| baseQrString | string | String dasar QRIS |
| logoPath | string | Path ke file logo (opsional) |

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
    merchantId: 'YOUR_MERCHANT_ID',
    apiKey: 'YOUR_API_KEY',
    baseQrString: 'YOUR_BASE_QR_STRING',
    logoPath: 'path/to/logo.png'
};

const qris = new QRISPayment(config);

async function main() {
    try {
        // Generate QR
        const { qrString, qrBuffer } = await qris.generateQR(10000);
        fs.writeFileSync('qr.png', qrBuffer);
        console.log('QR String:', qrString);

        // Cek pembayaran
        const result = await qris.checkPayment('REF123', 10000);
        console.log('Status pembayaran:', result);
    } catch (error) {
        console.error('Error:', error.message);
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

## Lisensi

MIT

## Kontribusi

Silakan buat pull request untuk kontribusi. Untuk perubahan besar, buka issue terlebih dahulu untuk mendiskusikan perubahan yang diinginkan.

## Support

Jika menemukan masalah atau memiliki pertanyaan, silakan buka issue di repository ini. 
