# autoft-orkut

Mutasi QRIS OrderKuota Client untuk Python

## Instalasi

```bash
pip install autoft-orkut

# Dengan fitur generate QR image
pip install autoft-orkut[imaging]
```

## Penggunaan

### Cek Mutasi

```python
from autoft_orkut import MutasiClient

client = MutasiClient("username", "userId:token")
result = client.get_mutasi()

print(result.to_json())
```

### Generate QRIS Dinamis

```python
from autoft_orkut import QRIS

qris_statis = "00020101021126670016COM.NOBUBANK.WWW..."

# Convert ke dinamis dengan nominal
qris_dinamis = QRIS.convert(qris_statis, 15000)

# Convert dengan kode unik random
result = QRIS.convert_unique(qris_statis, 15000)
print(f"QRIS: {result.qris}")
print(f"Kode Unik: {result.unique_code}")
print(f"Total: {result.total_amount}")
```

### Generate QR Image

```python
from autoft_orkut import QRIS

qris = QRIS(base_qr_string="00020101021126670016COM.NOBUBANK.WWW...")
image_bytes = qris.generate_dynamic_qr_image(15000)

with open("qris.png", "wb") as f:
    f.write(image_bytes)
```

## License

MIT
