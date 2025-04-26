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

## Contoh Simple

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
## Contoh Lengkap

```python
import time
import random
import string
from qris_payment import QRISPayment
from datetime import datetime, timedelta

# Konfigurasi
config = {
    'merchant_id': 'YOUR_MERCHANT_ID',
    'api_key': 'YOUR_API_KEY',
    'base_qr_string': 'YOUR_BASE_QR_STRING',
    'logo_path': 'path/to/logo.png'  # Opsional
}

# Inisialisasi QRIS Payment
qris = QRISPayment(config)

# Fungsi untuk generate random reference number
def generate_reference(length=10):
    characters = string.ascii_uppercase + string.digits
    return ''.join(random.choice(characters) for _ in range(length))

# Fungsi untuk generate amount dengan random cents
def generate_amount(base_amount):
    random_cents = random.randint(1, 99)
    return base_amount + random_cents

def main():
    try:
        # Generate random reference number
        reference_number = generate_reference()
        base_amount = 10000  # Nominal dasar (contoh: Rp 10.000)
        amount = generate_amount(base_amount)  # Tambah random cents
        
        print(f"\n=== QRIS Payment Demo ===")
        print(f"Reference Number: {reference_number}")
        print(f"Base Amount: Rp {base_amount:,}")
        print(f"Final Amount: Rp {amount:,}")
        print(f"Time Limit: 5 minutes")
        
        # Generate QR Code
        print("\nGenerating QR Code...")
        qr_result = qris.generate_qr(amount)
        
        # Simpan QR ke file
        qr_result['qr_image'].save('payment_qr.png')
        print("QR Code generated and saved as 'payment_qr.png'")
        print("Please scan the QR code and complete payment within 5 minutes")
        
        # Mulai timer 5 menit
        start_time = datetime.now()
        end_time = start_time + timedelta(minutes=5)
        
        print("\nWaiting for payment...")
        print(f"Payment must be completed before: {end_time.strftime('%H:%M:%S')}")
        
        # Cek status pembayaran setiap 10 detik
        while datetime.now() < end_time:
            result = qris.check_payment(reference_number, amount)
            
            if result['data']['status'] == 'PAID':
                print("\n=== Payment Successful! ===")
                print(f"Amount: Rp {result['data']['amount']:,}")
                print(f"Date: {result['data']['date']}")
                print(f"Brand Name: {result['data']['brand_name']}")
                print(f"Buyer Reference: {result['data']['buyer_reff']}")
                return
                
            time.sleep(10)  # Tunggu 10 detik sebelum cek lagi
            remaining_time = (end_time - datetime.now()).total_seconds()
            print(f"Time remaining: {int(remaining_time)} seconds", end='\r')
        
        print("\n\n=== Payment Time Expired! ===")
        print("The 5-minute payment window has ended.")
        print("Please generate a new QR code if you still want to make payment.")
        
    except Exception as e:
        print(f"\nError: {str(e)}")

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
