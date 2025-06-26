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
2. Instalasi dependensi dengan `composer install`
3. Buat File .env
4. Jalankan Migration
5. Jalankan Server Laravel
6. Jalankan Server Python (YOLO + OCR)


📂 Struktur Folder Penting
bash
Copy
Edit
├── app/                 # Laravel app
├── routes/              # File routing (web.php, api.php)
├── yolo-flask-api/      # Folder Python untuk YOLO & EasyOCR
├── public/              # Asset publik & upload hasil deteksi
├── database/            # Migration & seeders
├── resources/views/     # Blade templates




## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
