# QRIS Payment Python Package

Package Python untuk generate QRIS dan cek status pembayaran dengan fitur monitoring realtime.

## 🚀 Fitur Terbaru (v1.1.3)

- ✅ **Generate QRIS** dengan nominal tertentu
- ✅ **Tambah logo** di tengah QR
- ✅ **Cek status pembayaran** realtime
- ✅ **Filter waktu 10 menit** (lebih fleksibel)
- ✅ **Debug mode** untuk monitoring detail
- ✅ **Validasi format QRIS** yang robust
- ✅ **Perhitungan checksum CRC16**
- ✅ **Error handling** yang informatif
- ✅ **Type hints** untuk development yang lebih baik

## 📦 Instalasi

```bash
pip install qris-payment==1.1.3
```

## 🔑 Cara Mendapatkan Auth Token

Untuk mendapatkan `auth_username` dan `auth_token`, hubungi bot Telegram:

**@AutoFtBot** - Bot resmi untuk mendapatkan kredensial QRIS Payment

### Langkah-langkah:
1. Buka Telegram dan cari **@AuutooFtBot**
2. Kirim pesan `/start`
3. Ikuti instruksi untuk mendapatkan kredensial
4. Bot akan memberikan `auth_username` dan `auth_token`

## 🛠️ Penggunaan

### Inisialisasi

```python
from qris_payment import QRISPayment

config = {
    'auth_username': 'YOUR_AUTH_USERNAME',  # Dapat dari @AutoFtBot
    'auth_token': 'YOUR_AUTH_TOKEN',        # Dapat dari @AutoFtBot
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
        # Reference tidak dipakai untuk pengecekan, hanya amount
        result = qris.check_payment('REF123', 10000)
        print('Status pembayaran:', result)
    except Exception as e:
        print(f"Error: {str(e)}")
```

### Debug Mode

```python
# Untuk monitoring detail proses pengecekan
from qris_payment.payment_checker import PaymentChecker

checker = PaymentChecker({
    'auth_username': 'YOUR_AUTH_USERNAME',
    'auth_token': 'YOUR_AUTH_TOKEN'
}, debug=True)

result = checker.check_payment_status(None, 10000)
```

## 📋 Konfigurasi

| Parameter | Tipe | Deskripsi | Wajib |
|-----------|------|-----------|-------|
| auth_username | string | Username dari @AutoFtBot | Ya |
| auth_token | string | Token dari @AutoFtBot | Ya |
| base_qr_string | string | String dasar QRIS | Ya |
| logo_path | string | Path ke file logo (opsional) | Tidak |

## 📊 Response

### Generate QR

```python
{
    'qr_string': "000201010212...",  # String QRIS dengan checksum
    'qr_image': <PIL.Image.Image>    # Objek gambar QR
}
```

### Cek Pembayaran

```python
{
    'success': True,
    'data': {
        'status': 'PAID' | 'UNPAID',
        'amount': int,
        'date': str,        # Hanya jika status PAID
        'brand_name': str,  # Hanya jika status PAID
        'buyer_reff': str   # Hanya jika status PAID
    }
}
```

## ⚡ Contoh Realtime Payment

```python
import time
import random
from qris_payment import QRISPayment

config = {
    'auth_username': 'YOUR_AUTH_USERNAME',
    'auth_token': 'YOUR_AUTH_TOKEN',
    'base_qr_string': 'YOUR_BASE_QR_STRING',
    'logo_path': './logo.png'
}

def realtime_payment_test():
    qris = QRISPayment(config)
    
    # Generate QR dengan nominal random
    amount = 100 + random.randint(1, 99)
    result = qris.generate_qr(amount)
    result['qr_image'].save('qr.png')
    
    print(f'Amount: {amount}')
    print('QR saved as: qr.png')
    print('Silakan scan dan transfer tepat Rp', amount)
    
    # Monitor pembayaran (10 menit terakhir)
    start_time = time.time()
    while time.time() - start_time < 300:  # 5 menit timeout
        payment_result = qris.check_payment('REF', amount)
        if payment_result['success'] and payment_result['data']['status'] == 'PAID':
            print('🎉 Pembayaran berhasil!')
            print('Detail:', payment_result['data'])
            return
        time.sleep(3)
        print('Menunggu pembayaran...')
    
    print('Timeout: Pembayaran tidak diterima')

if __name__ == '__main__':
    realtime_payment_test()
```

## 🔍 Error Handling

Package ini akan melempar exception dengan pesan yang jelas:

- **Format QRIS tidak valid** - Pastikan base_qr_string mengandung "5802ID"
- **Nominal harus > 0** - Amount harus positif
- **Auth credentials tidak valid** - Cek username dan token dari @AutoFtBot
- **API tidak dapat diakses** - Cek koneksi internet
- **Response tidak valid** - Ada masalah dengan server API

## 📝 Changelog

### v1.1.3 (Latest)
- ✅ Filter waktu diperpanjang dari 5 menit ke 10 menit
- ✅ Debug mode untuk monitoring detail
- ✅ Error handling yang lebih robust
- ✅ Type hints untuk development
- ✅ Dokumentasi yang lebih lengkap

### v1.1.2
- Perbaikan bug minor
- Optimasi performa

### v1.1.1
- Fitur QRIS generation
- Payment checking

## 🖥️ Persyaratan Sistem

- **Python** >= 3.6
- **Dependencies:**
  - qrcode >= 7.4.2
  - Pillow >= 9.0.0
  - requests >= 2.28.0

## 📞 Support

- **Bot Telegram:** @AutoFtBot (untuk kredensial)
- **Repository:** [GitHub](https://github.com/AutoFtBot/qris-payment-py)
- **Email:** autoftbot@gmail.com

## 📄 Lisensi

MIT License

## 🤝 Kontribusi

Silakan buat pull request untuk kontribusi. Untuk perubahan besar, buka issue terlebih dahulu untuk mendiskusikan perubahan yang diinginkan.

---

**Dibuat dengan ❤️ oleh AutoFtBot Team** 
