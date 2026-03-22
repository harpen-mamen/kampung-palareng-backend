# Backend Laravel API - Portal Kampung Palareng

Backend ini menyediakan REST API untuk autentikasi admin, data keluarga, rumah, bantuan, layanan surat, berita, statistik, peta digital, dan ekspor laporan.

## Menjalankan backend

1. Salin `.env.example` menjadi `.env`.
2. Buat database `portal_kampung_palareng` di phpMyAdmin/XAMPP.
3. Pastikan konfigurasi database:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portal_kampung_palareng
DB_USERNAME=root
DB_PASSWORD=
```

4. Jalankan:

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

5. Akses API di `http://127.0.0.1:8000/api`.

## Kredensial seed

- `admin@palareng.id` / `password`
- `operator@palareng.id` / `password`
