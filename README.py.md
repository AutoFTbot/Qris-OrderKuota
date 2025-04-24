# QRIS Payment Python Package

Package Python untuk generate QRIS dan cek status pembayaran.

## Fitur

- Generate QRIS dengan nominal tertentu
- Tambah logo di tengah QR
- Cek status pembayaran
- Validasi format QRIS
- Perhitungan checksum CRC16

## Instalasi

```bash
pip install qris-payment
```

## Penggunaan

### Inisialisasi

```python
from qris_payment import QRISPayment

config = {
    'merchant_id': 'YOUR_MERCHANT_ID',
    'api_key': 'YOUR_API_KEY',
    'base_qr_string': 'YOUR_BASE_QR_STRING',
    'logo_path': 'path/to/logo.png'  # Opsional
}

qris = QRISPayment(config)
```

### Generate QRIS

```python
def generate_qr():
    try:
        result = qris.generate_qr(10000)
        
        # Simpan QR ke file
        result['qr_image'].save('qr.png')
        print('QR String:', result['qr_string'])
    except Exception as e:
        print(f"Error: {str(e)}")
```

### Cek Status Pembayaran

```python
def check_payment():
    try:
        result = qris.check_payment('REF123', 10000)
        print('Status pembayaran:', result)
    except Exception as e:
        print(f"Error: {str(e)}")
```

## Konfigurasi

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| merchant_id | string | ID Merchant QRIS |
| api_key | string | API Key untuk cek pembayaran |
| base_qr_string | string | String dasar QRIS |
| logo_path | string | Path ke file logo (opsional) |

## Response

### Generate QR

```python
{
    'qr_string': "000201010212...",  # String QRIS
    'qr_image': <PIL.Image.Image>  # Objek gambar QR
}
```

### Cek Pembayaran

```python
{
    'success': True,
    'data': {
        'status': 'PAID' | 'UNPAID',
        'amount': int,
        'reference': str,
        'date': str,  # Hanya jika status PAID
        'brand_name': str,  # Hanya jika status PAID
        'buyer_reff': str  # Hanya jika status PAID
    }
}
```

## Error Handling

Package ini akan melempar exception dengan pesan yang jelas jika terjadi masalah:

- Format QRIS tidak valid
- Gagal generate QR
- Gagal cek status pembayaran
- API key tidak valid
- dll

## Contoh Lengkap

```python
from qris_payment import QRISPayment

config = {
    'merchant_id': 'YOUR_MERCHANT_ID',
    'api_key': 'YOUR_API_KEY',
    'base_qr_string': 'YOUR_BASE_QR_STRING',
    'logo_path': 'path/to/logo.png'
}

qris = QRISPayment(config)

def main():
    try:
        # Generate QR
        result = qris.generate_qr(10000)
        result['qr_image'].save('qr.png')
        print('QR String:', result['qr_string'])

        # Cek pembayaran
        payment_result = qris.check_payment('REF123', 10000)
        print('Status pembayaran:', payment_result)
    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    main()
```

## Persyaratan Sistem

- Python >= 3.6
- Dependencies:
  - qrcode >= 7.4.2
  - Pillow >= 9.0.0
  - requests >= 2.28.0

## Lisensi

MIT

## Kontribusi

Silakan buat pull request untuk kontribusi. Untuk perubahan besar, buka issue terlebih dahulu untuk mendiskusikan perubahan yang diinginkan.

## Support

Jika menemukan masalah atau memiliki pertanyaan, silakan buka issue di repository ini. 
