# GadungSense IoT
Sistem monitoring cerdas untuk produksi kerupuk gadung berbasis IoT.

---

## Pertama Kali Pakai Git? Mulai di Sini
Sebelum ngapa-ngapain, kasih tahu Git siapa kamu. Ini cukup **sekali aja** di laptop masing-masing.
```bash
git config --global user.name "nama_kamu"
git config --global user.email "email_kamu@gmail.com"
```
> Gunakan email yang sama dengan akun GitHub kamu.

---

## Clone Repo
Clone = download semua isi repo ke laptop kamu. Lakukan **sekali** di awal.
```bash
git clone https://github.com/ekka-lks/gadungan.git
cd gadungan
```

---

## Setup Awal (Wajib Setelah Clone)

> **Kenapa perlu setup ini?**
> Laravel itu seperti "mesin" aplikasi kita. Setelah clone, mesinnya ada tapi belum dinyalakan — belum ada bahan bakar (dependencies), belum ada konfigurasi, dan belum ada tabelnya di database. Setup ini yang ngurus semua itu.

### Sebelum mulai, pastikan sudah punya:
- **PHP** — cek dengan `php -v` di terminal. Kalau belum ada, install XAMPP (sudah include PHP + MySQL).
- **Composer** — cek dengan `composer -v`. Kalau belum, download di [getcomposer.org](https://getcomposer.org/download/)
- **XAMPP** — pastikan Apache & MySQL-nya sudah **running** (hijau di XAMPP Control Panel)

---

### Langkah 1 — Install dependencies
```bash
composer install
```
**Composer** itu seperti "toko" yang otomatis download semua library yang dibutuhkan Laravel. Perlu koneksi internet dan agak lama pertama kali (~1-2 menit).

---

### Langkah 2 — Buat file konfigurasi
Duplikat file `.env.example`, rename jadi `.env` (klik kanan di VS Code atau File Explorer).

Lalu jalankan:
```bash
php artisan key:generate
```
> **Apa itu `.env`?** File pengaturan rahasia aplikasi — berisi info database, dll. File ini **tidak ikut ke GitHub**, jadi setiap orang harus bikin sendiri.
>
> **Apa itu `php artisan`?** "Remote control" Laravel. `key:generate` artinya bikin kunci keamanan untuk aplikasi ini.

---

### Langkah 3 — Atur koneksi database
Buka file `.env`, cari bagian ini dan sesuaikan:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iot_gadungan
DB_USERNAME=root
DB_PASSWORD=
```
Untuk pengguna XAMPP, biasanya sudah benar seperti di atas — `DB_PASSWORD` dikosongkan saja.

---

### Langkah 4 — Buat database
Buka browser → pergi ke `http://localhost/phpmyadmin` → klik **New** di sidebar kiri → ketik `iot_gadungan` → klik **Create**.

---

### Langkah 5 — Buat tabel-tabelnya
```bash
php artisan migrate
```
> **Apa itu migrate?** Perintah yang otomatis bikin tabel-tabel di database sesuai yang sudah didefinisikan di kode. Kamu tidak perlu bikin tabel manual di phpMyAdmin.

Kalau berhasil, bakal muncul tulisan seperti:
```
INFO  Running migrations.
✓ ... created successfully
```

---

### Langkah 6 — Jalankan aplikasi
```bash
php artisan serve
```
Buka browser → `http://localhost:8000` → aplikasi sudah jalan! 🎉

> Untuk menghentikan server, tekan `Ctrl + C` di terminal.

---

## Git Pull
Pull = ambil update terbaru dari teman-teman. Lakukan ini **setiap kali mau mulai kerja**.
```bash
git pull
```

---

## Git Push
Push = kirim perubahan kamu ke repo biar bisa diakses yang lain.
```bash
git add .
git commit -m "pesan commit kamu"
git push origin main
```
- `git add .` → masukkan semua file yang diubah ke "antrian"
- `git commit -m "..."` → simpan dengan catatan (contoh: `"tambah halaman dashboard"`)
- `git push origin main` → kirim ke GitHub

---

## Alur Kerja Sehari-hari
```
1. git pull               ← ambil update dulu
2. php artisan serve      ← nyalakan server
3. ... kerja, edit file ...
4. git add .
5. git commit -m "..."
6. git push               ← kirim ke repo
```

---

## Kalau Ada Update dari Teman (Migration Baru)
Kalau setelah `git pull` ada perubahan di folder `database/migrations/`, jalankan:
```bash
php artisan migrate
```
Ini akan nambah tabel baru yang dibuat teman kamu. Kalau dilewatin, aplikasi bisa error.

---

## File `.env` Hilang / Error Konfigurasi?
Ulangi dari Langkah 2 di atas (duplikat ulang dari `.env.example`).