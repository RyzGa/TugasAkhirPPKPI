# Setup Cloudinary untuk NusaBites

## Langkah 1: Daftar Akun Cloudinary (GRATIS)

1. Buka https://cloudinary.com/users/register/free
2. Daftar dengan email Anda (atau gunakan Google Sign-in)
3. Verifikasi email Anda
4. Login ke Dashboard Cloudinary

## Langkah 2: Dapatkan Credentials

Setelah login, Anda akan melihat Dashboard dengan informasi berikut:

```
Cloud name: xxxxxx
API Key: xxxxxxxxxxxxxx
API Secret: xxxxxxxxxxxxxxxxxxxxxx
```

**PENTING:** Simpan informasi ini dengan aman!

## Langkah 3: Update Konfigurasi di Website

1. Buka file: `config/cloudinary.php`
2. Ganti nilai berikut dengan credentials Anda:

```php
define('CLOUDINARY_CLOUD_NAME', 'your_cloud_name');  // Ganti dengan Cloud Name Anda
define('CLOUDINARY_API_KEY', 'your_api_key');        // Ganti dengan API Key Anda
define('CLOUDINARY_API_SECRET', 'your_api_secret');  // Ganti dengan API Secret Anda
```

**Contoh:**
```php
define('CLOUDINARY_CLOUD_NAME', 'nusabites');
define('CLOUDINARY_API_KEY', '123456789012345');
define('CLOUDINARY_API_SECRET', 'abcdefghijklmnopqrstuvwxyz123456');
```

## Langkah 4: (Opsional) Buat Upload Preset

Di Dashboard Cloudinary:
1. Klik **Settings** (ikon gear) di kanan atas
2. Pilih tab **Upload**
3. Scroll ke bawah ke **Upload presets**
4. Klik **Add upload preset**
5. Isi:
   - Preset name: `nusabites`
   - Signing mode: `Unsigned` (untuk kemudahan)
   - Folder: `nusabites`
6. Klik **Save**

## Langkah 5: Testing

1. Login ke website NusaBites
2. Coba edit profile dan upload foto profil baru
3. Coba tambah resep dengan gambar
4. Periksa di Cloudinary Dashboard → Media Library, apakah gambar berhasil terupload

## Fitur yang Menggunakan Cloudinary

✅ **Upload Foto Profil** (Edit Profile)
- Folder: `nusabites/avatars/`
- Max size: 2MB
- Format: JPG, PNG, WEBP

✅ **Upload Gambar Resep** (Tambah/Edit Resep)
- Folder: `nusabites/recipes/`
- Max size: 5MB
- Format: JPG, PNG, WEBP

## Keuntungan Menggunakan Cloudinary

1. ✅ **Gratis 25 GB storage** dan 25 GB bandwidth/bulan
2. ✅ **Auto CDN** - gambar loading lebih cepat di seluruh dunia
3. ✅ **Auto optimization** - gambar di-compress otomatis
4. ✅ **Responsive images** - auto resize sesuai device
5. ✅ **Backup otomatis** - data aman di cloud
6. ✅ **Tidak perlu manage server storage**

## Troubleshooting

### Error: "Gagal mengupload gambar ke Cloudinary"

1. Periksa credentials di `config/cloudinary.php` sudah benar
2. Periksa koneksi internet server
3. Periksa apakah CURL enabled di PHP (`php -m | grep curl`)
4. Periksa error log: `tail -f /path/to/php/error.log`

### Gambar tidak muncul di website

1. Periksa di Cloudinary Dashboard → Media Library apakah gambar ada
2. Periksa URL gambar di database (harus dimulai dengan `https://res.cloudinary.com/`)
3. Clear browser cache

### Quota exceeded

Jika sudah melebihi 25GB/bulan:
1. Upgrade ke plan berbayar, atau
2. Tunggu hingga bulan berikutnya (quota reset)

## Migrasi Gambar Lama (Opsional)

Jika ingin memindahkan gambar yang sudah ada di `uploads/` ke Cloudinary:

1. Buat script migrasi (bisa request ke developer)
2. Upload manual via Cloudinary Dashboard
3. Update URL di database

## Support

Dokumentasi lengkap Cloudinary:
- https://cloudinary.com/documentation
- https://cloudinary.com/documentation/php_integration
