<p align="center">
  <img src="qriss.png" alt="QRIS Logo" width="150"/>
</p>

<h1 align="center">🚀 QRIS Payment Package</h1>

<p align="center">
  <strong>Paket powerful untuk generate QRIS dan cek status pembayaran secara real-time 🔄</strong>
</p>

<p align="center">
  <a href="https://www.npmjs.com/package/qris-payment">
    <img src="https://img.shields.io/npm/v/qris-payment?style=for-the-badge&logo=npm&color=crimson" alt="npm version" />
  </a>
  <a href="https://pypi.org/project/qris-payment/">
    <img src="https://img.shields.io/pypi/v/qris-payment?style=for-the-badge&logo=python&color=blue" alt="PyPI version" />
  </a>
  <a href="https://pkg.go.dev/github.com/AutoFTbot/OrderKuota-go">
    <img src="https://img.shields.io/badge/Go-1.0.0-blue?style=for-the-badge&logo=go" alt="Go version" />
  </a>
</p>

<p align="center">
  <a href="https://github.com/AutoFTbot/Qris-OrderKuota/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/AutoFTbot/Qris-OrderKuota?style=for-the-badge&color=orange" alt="License" />
  </a>
  <a href="https://github.com/AutoFTbot/Qris-OrderKuota/stargazers">
    <img src="https://img.shields.io/github/stars/AutoFTbot/Qris-OrderKuota?style=for-the-badge&logo=github" alt="Stars" />
  </a>
  <a href="https://github.com/AutoFTbot/Qris-OrderKuota/network">
    <img src="https://img.shields.io/github/forks/AutoFTbot/Qris-OrderKuota?style=for-the-badge&logo=github" alt="Forks" />
  </a>
  <a href="https://github.com/AutoFTbot/Qris-OrderKuota/issues">
    <img src="https://img.shields.io/github/issues/AutoFTbot/Qris-OrderKuota?style=for-the-badge&logo=github" alt="Issues" />
  </a>
</p>

<p align="center">
  <a href="#-fitur-utama">Fitur</a> •
  <a href="#-cara-mendapatkan-your_base_qr_string">Cara Penggunaan</a> •
  <a href="#-dokumentasi">Dokumentasi</a> •
  <a href="#-dukungan">Dukungan</a>
</p>

---

## 🌟 Fitur Utama

<div align="center">

| Fitur | Deskripsi |
|:---:|:---|
| ✅ Generate QRIS | Generate QRIS untuk nominal tertentu |
| 🖼️ Custom Logo | Tambah logo custom di tengah QR |
| 📡 Real-time Status | Cek status pembayaran dari API |
| 🛡️ Validasi | Validasi format QRIS |
| 📊 Checksum | Hitung checksum CRC16 |

</div>

---

## 🧾 Cara Mendapatkan `YOUR_BASE_QR_STRING`

Untuk menggunakan package ini, kamu membutuhkan **QR base string** dari QRIS. Ikuti langkah berikut:

1. **Siapkan Gambar QRIS** yang kamu miliki.  
2. Buka situs:  
   👉 [https://www.imagetotext.info/qr-code-scanner](https://www.imagetotext.info/qr-code-scanner)  
3. Upload gambar QRIS ke situs tersebut.  
4. Tunggu hingga proses scan selesai.  
5. Salin hasil **output QR string** yang muncul.  
6. Gunakan hasil tersebut sebagai nilai `YOUR_BASE_QR_STRING`.

> 💡 **Contoh hasil:**  
> ```
> 00020101021226690014ID.CO.QRIS.WWW01189360091311520010303120123456789040415ID10203040506070809051003UME51440014ID.CO.BANK90203123456303201234567890503...
> ```

---

## 📚 Dokumentasi

<div align="center">

| Bahasa | Dokumentasi |
|:---:|:---|
| <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" width="20" height="20"/> JavaScript | [`README.js.md`](README.js.md) |
| <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/python/python-original.svg" width="20" height="20"/> Python | [`README.py.md`](README.py.md) |
| <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/go/go-original.svg" width="20" height="20"/> Go | [`README.go.md`](README.go.md) <br/> <sub>⚠️ Fitur Custom Logo tidak tersedia</sub> |

</div>

> 📖 **Dokumentasi Lengkap:**  
> 👉 [https://qrisorderkuota.hashnode.space](https://qrisorderkuota.hashnode.space)

---

## ❤️ Dukungan

Jika kamu suka proyek ini dan ingin mendukung pengembangannya, kamu bisa:

- ⭐ Memberi bintang repo ini  
- 🔄 Share ke teman-teman developer lainnya  
- 💰 Donasi melalui QRIS berikut:

<p align="center">
  <img src="https://raw.githubusercontent.com/AutoFTbot/AutoFTbot/refs/heads/main/assets/QRIS.jpg" alt="Donasi via QRIS" width="300"/>
</p>

---

<div align="center">
  <sub>Built with ❤️ by <a href="https://github.com/AutoFTbot">AutoFTbot</a></sub>
</div>
