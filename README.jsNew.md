# AutoFT Orkut

Library TypeScript untuk mengambil data mutasi QRIS dengan fitur **Anti-Deteksi** (Consistent Device ID) dan **QRIS Generator**.

## Fitur Utama

- **🛡️ Anti-Deteksi**: Menggunakan algoritma "Seed-based Device ID". Username Anda akan selalu menghasilkan ID HP, Model, dan User-Agent yang sama.
- **⚡ Ringan**: Menggunakan `axios` untuk koneksi yang cepat.
- **🖼️ QRIS Generator**: Membuat QRIS dinamis dan gambar QRIS.

## Instalasi

```bash
npm install autoft-orkut
```

## Cara Penggunaan

```typescript
import { MutasiClient } from 'autoft-orkut';

async function main() {
  // Ganti dengan kredensial Anda
  const username = 'username_anda';
  const token = 'userid:token_anda';

  const client = new MutasiClient(username, token);

  try {
    const result = await client.getMutasi();
    
    if (result.status) {
      console.log('Berhasil!');
      console.log(`Merchant: ${result.merchant}`);
      console.log('Data Mutasi:', result.data);
    } else {
      console.log('Gagal:', result.message);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

main();
```

## Generate QRIS Dinamis

Anda juga bisa membuat QRIS dinamis (dengan nominal tertentu) dari QRIS statis:

```typescript
import { QRIS } from 'autoft-orkut';

const qrisStatis = "00020101021126570014ID.LINKAJA...";
const nominal = 15000;

const qrisBaru = QRIS.convert(qrisStatis, nominal);
console.log(qrisBaru); // Output: QRIS string dengan nominal 15000
```

### Generate QRIS dengan Kode Unik (Random 1-1000)

Untuk mempermudah identifikasi pembayaran, Anda bisa menambahkan kode unik acak:

```typescript
const result = QRIS.convertUnique("000201...", 15000);

console.log(result.uniqueCode); // Contoh: 123
console.log(result.totalAmount); // Contoh: 15123
console.log(result.qris); // QRIS string dengan nominal 15123
```

### Generate QRIS Image

```typescript
import { QRIS } from 'autoft-orkut';
import * as fs from 'fs';

const qrisGen = new QRIS({
  baseQrString: "000201010211..."
});

// Generate QRIS Dinamis dulu
const qrisString = QRIS.convert("000201010211...", 15000);

// Buat gambar
const buffer = await qrisGen.generateQRImage(qrisString);
fs.writeFileSync('qris.png', buffer);
```

## Struktur Data Response

```typescript
interface MutasiItem {
  date: string;       // Tanggal transaksi (YYYY-MM-DD HH:MM)
  amount: string;     // Jumlah (tanpa Rp/titik)
  type: 'CR' | 'DB';  // CR = Masuk, DB = Keluar
  qris: string;       // Tipe QRIS
  brand_name: string; // Nama pengirim (DANA, OVO, dll)
  issuer_reff: string;// Reff ID dari issuer
  buyer_reff: string; // Nama pengirim / Keterangan
  balance: string;    // Saldo akhir
}
```

## Lisensi

Private / Proprietary. Dilarang mendistribusikan ulang tanpa izin.
