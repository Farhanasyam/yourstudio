# Panduan Deploy ke Hosting

Checklist untuk memasang YourStudio di hosting. Kerjakan berurutan.

## 1. Syarat hosting

- PHP 8.1+ dan MySQL/MariaDB.
- **HTTPS wajib.** Tanpa HTTPS, fitur kasir offline (halaman kasir tetap bisa dibuka saat internet mati) tidak aktif.
- Document root diarahkan ke folder `public/`.

## 2. File `.env` di server

Salin dari `.env.example`, lalu ubah minimal bagian berikut:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

LOG_LEVEL=error
SESSION_SECURE_COOKIE=true

DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

- `APP_DEBUG=false` wajib. Kalau `true`, pesan error bisa menampilkan detail server dan database ke pengunjung.
- Buat `APP_KEY` sekali saja dengan `php artisan key:generate`. Jangan diganti setelah aplikasi dipakai, karena semua sesi login akan batal.

## 3. Perintah pemasangan

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate        # hanya saat pemasangan pertama
php artisan migrate --force
php artisan db:seed --force     # hanya saat pemasangan pertama (akun awal & pengaturan)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setiap kali memperbarui kode di server, jalankan:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 4. Wajib setelah pemasangan pertama

1. **Ganti semua password akun bawaan seeder** (`superadmin@yourstudio.com`, `admin@yourstudio.com`, `kasir1@...`, `kasir2@...`) lewat menu Profile Settings / User Management. Password bawaan tercantum di kode, jadi siapa pun yang melihat kodenya bisa login.
2. Hapus akun contoh yang tidak dipakai.
3. Isi **harga jual** semua barang (barang dengan harga Rp 0 ditolak di kasir).
4. Periksa **System Settings**: nama toko, alamat, dan teks struk.

## 5. Backup

Aktifkan **backup database otomatis harian** dari panel hosting. Kalau tidak tersedia, jadwalkan (cron) perintah berikut:

```bash
mysqldump -u USER -pPASSWORD NAMA_DB | gzip > ~/backup/yourstudio-$(date +\%F).sql.gz
```

Simpan salinan backup di luar server juga (misalnya Google Drive), dan sesekali coba restore supaya yakin file backup-nya bisa dipakai.

## 6. Perangkat kasir

- Setelah login, buka halaman **Kasir** sekali saat online di setiap perangkat. Data barang dan halaman kasir akan tersimpan untuk dipakai offline.
- Jangan pakai mode incognito, dan jangan hapus data browser di perangkat kasir. Transaksi offline yang belum terkirim tersimpan di browser tersebut.
- Siapkan internet cadangan (hotspot HP).
