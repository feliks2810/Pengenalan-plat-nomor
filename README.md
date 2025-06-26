# 🚨 PINHEL - Pengenalan Plat Nomor Tanpa Helm

PINHEL adalah aplikasi berbasis web yang dikembangkan untuk mendeteksi pelanggaran lalu lintas, khususnya pengendara sepeda motor yang tidak mengenakan helm. Aplikasi ini menggunakan teknologi **YOLOv8** untuk deteksi objek dan **EasyOCR** untuk membaca plat nomor kendaraan secara otomatis.

---

## 🎯 Tujuan Proyek

Meningkatkan keselamatan lalu lintas dengan membantu instansi atau pihak keamanan untuk mengidentifikasi pengendara yang melanggar aturan menggunakan sistem pemantauan otomatis.

---

## 🛠 Teknologi yang Digunakan

- **Laravel** – Sebagai framework backend & frontend utama
- **YOLOv8** – Untuk mendeteksi pengendara dan kondisi helm
- **EasyOCR** – Untuk pembacaan teks plat nomor
- **Python + Flask** – Untuk integrasi model AI (YOLO & OCR)
- **SQLite/MySQL** – Sebagai basis data
- **Tailwind CSS** – Untuk styling frontend
- **OpenCV** – Untuk pemrosesan video dan gambar (di sisi Python)

---

## 🧩 Fitur Utama

- ✅ Deteksi kendaraan dengan atau tanpa helm melalui kamera
- ✅ Pengenalan otomatis plat nomor kendaraan
- ✅ Dashboard admin untuk memantau dan merekap pelanggaran
- ✅ Riwayat pelanggaran disimpan ke database
- ✅ Komunikasi antara sistem AI dan Laravel melalui API

---


---

## 🧑‍💻 Cara Instalasi (Local Development) Clone repository

1. Clone repository ini
   ```bash
    git clone https://github.com/feliks2810/Pengenalan-plat-nomor.git
    cd Pengenalan-plat-nomor

3. Instalasi dependensi
   ```bash
   composer install
    npm install && npm run dev

5. Buat File .env
   ```bash
   cp .env.example .env
    php artisan key:generate
7. Jalankan Migration
   ```bash
   php artisan migrate
9. Jalankan Server Laravel
    ```bash
    php artisan serve
11. Jalankan Server Python (YOLO + OCR)
    ```bash
    python app.py



## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
