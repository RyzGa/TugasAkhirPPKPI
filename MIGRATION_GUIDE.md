# Panduan Migrasi Gambar ke Cloudinary

## ⚠️ PENTING - Baca Dulu!

**Sebelum menjalankan script migrasi:**
1. ✅ **Backup database** terlebih dahulu
2. ✅ Pastikan Cloudinary credentials sudah benar di `config/cloudinary.php`
3. ✅ Pastikan koneksi internet stabil
4. ✅ Cek dulu gambar yang sudah terupload di Cloudinary Media Library

## 📋 Script yang Tersedia

### 1. `migrate_avatars.php` - Migrasi Foto Profil
Memindahkan semua foto profil dari `uploads/avatars/` ke Cloudinary folder `nusabites/avatars/`

### 2. `migrate_recipes.php` - Migrasi Gambar Resep
Memindahkan semua gambar resep dari `uploads/recipes/` ke Cloudinary folder `nusabites/recipes/`

## 🚀 Cara Menjalankan

### Opsi 1: Via Command Line (Recommended)

```bash
# 1. Masuk ke folder project
cd C:\xamppp\htdocs\NusaBites

# 2. Jalankan migrasi avatars
php migrate_avatars.php

# 3. Jalankan migrasi recipes
php migrate_recipes.php
```

### Opsi 2: Via Browser

1. Buka browser
2. Akses URL:
   - `http://localhost/NusaBites/migrate_avatars.php` - untuk foto profil
   - `http://localhost/NusaBites/migrate_recipes.php` - untuk gambar resep

## 📊 Output Script

Script akan menampilkan:
```
=== MIGRASI AVATARS KE CLOUDINARY ===

Ditemukan 2 avatar yang akan dimigrasi...

Processing user #4 - Rizky Angga Wibowo...
  Avatar lokal: uploads/avatars/1762877049_7b4345bb86f5.png
  Uploading ke Cloudinary...
  ✅ Upload berhasil: https://res.cloudinary.com/di9ocdzxe/image/upload/...
  ✅ Database updated

Processing user #11 - Rizky Angga Wibowo...
  Avatar lokal: uploads/avatars/1763182361_dbacc9bf8ff8.jpg
  Uploading ke Cloudinary...
  ✅ Upload berhasil: https://res.cloudinary.com/di9ocdzxe/image/upload/...
  ✅ Database updated

=== RINGKASAN ===
Berhasil: 2
Gagal: 0

=== SELESAI ===
```

## ✅ Verifikasi Setelah Migrasi

### 1. Cek di Cloudinary Dashboard
1. Login ke https://cloudinary.com
2. Klik **Media Library**
3. Buka folder **nusabites**
4. Lihat subfolder:
   - **avatars** - harus ada foto profil
   - **recipes** - harus ada gambar resep

### 2. Cek di Database
```sql
-- Cek avatars
SELECT id, name, avatar FROM users WHERE avatar LIKE '%cloudinary%';

-- Cek recipes
SELECT id, title, image FROM recipes WHERE image LIKE '%cloudinary%';
```

### 3. Cek di Website
1. Buka halaman profile
2. Cek apakah foto profil muncul
3. Buka halaman detail resep
4. Cek apakah gambar resep muncul

## 🗑️ Menghapus File Lokal (Opsional)

Secara default, script **TIDAK** menghapus file lokal setelah upload. Jika ingin menghapus:

1. Buka file `migrate_avatars.php` atau `migrate_recipes.php`
2. Cari baris:
   ```php
   // unlink($localPath);
   // echo "  ✅ File lokal dihapus\n";
   ```
3. Hapus `//` (uncomment):
   ```php
   unlink($localPath);
   echo "  ✅ File lokal dihapus\n";
   ```
4. Jalankan ulang script

## 🔄 Rollback (Jika Ada Masalah)

Jika migrasi gagal atau ada masalah:

1. **Restore database** dari backup
2. **File lokal masih ada** (selama tidak dihapus manual)
3. Perbaiki masalah (credentials, koneksi, dll)
4. Jalankan ulang script

## ⚡ Tips

1. **Test dulu dengan 1 file:**
   - Edit script, tambahkan `LIMIT 1` di query
   - Jalankan dan cek hasilnya
   - Jika berhasil, jalankan untuk semua

2. **Monitoring:**
   - Perhatikan output script untuk error
   - Cek Cloudinary quota (max 25GB/month gratis)
   - Cek size total gambar sebelum migrasi

3. **Jika error "CURL not enabled":**
   ```bash
   # Cek apakah CURL enabled
   php -m | grep curl
   
   # Jika tidak ada, enable di php.ini:
   # Hapus ; di depan: extension=curl
   # Restart Apache
   ```

## 📞 Troubleshooting

### Error: "Failed to upload"
- Cek Cloudinary credentials di `config/cloudinary.php`
- Cek koneksi internet
- Cek Cloudinary quota

### Error: "File tidak ditemukan"
- Cek path file di database
- Cek apakah file benar-benar ada di folder uploads/

### Error: "Database update failed"
- Cek koneksi database
- Cek permissions user database

## 🎯 Setelah Migrasi Selesai

1. ✅ Semua gambar baru akan langsung ke Cloudinary
2. ✅ Gambar lama sudah di Cloudinary
3. ✅ Website loading lebih cepat (CDN)
4. ✅ Storage server berkurang
5. ✅ Backup otomatis di cloud

---

**Siap untuk migrasi?** Jalankan perintah di atas! 🚀
