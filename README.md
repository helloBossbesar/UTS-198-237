# UTS Pemrograman Web Service - Dashboard Biodata Tim

Aplikasi web dashboard biodata anggota tim dengan fitur CRUD lengkap dan upload foto, dibangun menggunakan Laravel + PHP untuk backend, serta HTML, CSS, JavaScript (Fetch API + Bootstrap 5.3) untuk frontend tanpa framework JS eksternal maupun build step.

---

## Pembuat Proyek
- **Ketua Tim**: (Masukkan Nama ketua)
- **Anggota Tim**: (Masukkan Nama Anggota)

---

## Fitur Utama
1. **CRUD Biodata Lengkap**:
   - Menampilkan seluruh data tim dalam format kartu responsif.
   - Kartu pertama berupa tombol khusus ("+") untuk menambah biodata baru.
   - Modal Bootstrap tunggal dinamis untuk mode Tambah dan Edit dengan auto-reset state.
   - Fitur hapus data menggunakan konfirmasi modal interaktif.
2. **Upload & Preview Foto**:
   - Preview foto seketika saat memilih file.
   - Validasi klien dan server (tipe gambar, maksimal ukuran 5 MB).
   - Penggantian foto lama otomatis saat diperbarui, serta penghapusan file otomatis dari disk saat data dihapus.
   - Placeholder siluet SVG elegan jika data tidak memiliki foto.
3. **UX & Keamanan**:
   - XSS sanitasi: seluruh teks pengguna di-escape sebelum masuk DOM.
   - CSRF protection via meta tag `X-CSRF-TOKEN` dan header AJAX.
   - Pencegahan double submission dengan status disabled pada tombol saat memproses.
   - Alert dismissible dinamis dengan auto-dismiss setelah 4 detik.

---

## Daftar Endpoint API

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/` | Menampilkan halaman utama dashboard (`home.blade.php`) |
| `GET` | `/api/biodata` | Mengambil seluruh data biodata (urut ID ASC) |
| `GET` | `/api/biodata/{id}` | Mengambil detail 1 data biodata berdasarkan ID |
| `POST` | `/api/biodata` | Menambahkan data biodata baru (mendukung upload foto) |
| `POST` / `PUT` | `/api/biodata/{id}` | Memperbarui biodata (frontend mengirim `POST` dengan field `_method=PUT`) |
| `DELETE` | `/api/biodata/{id}` | Menghapus record biodata beserta file foto terkait dari disk |

Seluruh respon API berformat JSON:
```json
{
  "success": true,
  "message": "Pesan deskriptif status",
  "data": { ... }
}
```

---

## Cara Menjalankan dari Hasil Clone

Ikuti langkah-langkah berikut untuk menjalankan aplikasi:

1. **Clone repositori dan masuk ke direktori proyek**:
   ```bash
   git clone <URL_REPOSITORY>
   cd uts-pem-web
   ```

2. **Install dependensi Composer**:
   ```bash
   composer install
   ```

3. **Setup environment file**:
   Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   # Di Windows Command Prompt / PowerShell:
   # copy .env.example .env
   ```
   *(Pastikan nilai `DB_CONNECTION=sqlite`, `SESSION_DRIVER=file`, dan `CACHE_STORE=file` sudah aktif)*

4. **Buat file database SQLite kosong**:
   ```bash
   # Di Linux / macOS:
   touch database/database.sqlite

   # Di Windows PowerShell:
   New-Item -ItemType File -Force -Path database/database.sqlite
   ```

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Database Migration & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

7. **Jalankan Development Server**:
   ```bash
   php artisan serve
   ```

8. **Buka aplikasi di browser**:
   Kunjungi [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## Perintah Menghubungkan ke GitHub
Jika ingin mengunggah repositori ini ke remote GitHub Anda:
```bash
git remote add origin <URL>
git branch -M main
git push -u origin main
```
