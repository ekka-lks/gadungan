# GadungSense IoT

Sistem monitoring cerdas untuk produksi kerupuk gadung berbasis IoT.

---

## Pertama Kali Pakai Git? Mulai

Sebelum ngapa-ngapain, lu perlu kasih tahu Git siapa elu. Ini cukup dilakuin **sekali aja** di laptop lu.

```bash
git config --global user.name "nama_lu"
git config --global user.email "email_elu@gmail.com"
```

> Gunakan email yang sama dengan akun Github lu.

---

## Clone Repo

Clone = ngambil/download semua isi repo ke laptop lu. Lakuin ini **sekali** di awal.

```bash
git clone https://github.com/ekka-lks/gadungan.git
cd gadungan (terminal) / open folder aja di vscode nya
```

Setelah ini lu udah ada di dalam folder proyek, dan siap mulai kerja.

---

## Git Pull

Pull = ambil update terbaru dari temen-temen yang udah push ke repo. Lakuin ini **setiap kali mau mulai kerja** biar file lu selalu up-to-date.

```bash
git pull origin main atau git pull aja
```

---

## Git Push

Push = kirim perubahan yang udah lu buat ke repo biar bisa diakses temen-temen lain.

```bash
git add .
git commit -m "pesan commit kamu"
git push origin main
```

Penjelasan tiap langkahnya:
- `git add .` → masukin semua file yang kamu ubah ke "antrian"
- `git commit -m "..."` → simpan perubahan dengan catatan/pesan (isi pesan yang jelas, contoh: `"tambah sensor suhu"`)
- `git push origin main` → kirim ke GitHub

---

## Alur Kerja Sehari-hari

Biar nggak bentrok sama kerjaan temen, ikutin urutan ini setiap mau kerja:

```
1. git pull   ← ambil update dulu
2. ... kerja, edit file ...
3. git add .
4. git commit -m "..."
5. git push   ← kirim ke repo
```