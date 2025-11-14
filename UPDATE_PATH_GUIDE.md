# Panduan Update Path Setelah Reorganisasi Folder

## Ringkasan Perubahan

File-file telah dipindahkan ke struktur folder yang lebih rapi:

### File yang Dipindahkan:

1. **Authentication** (`pages/auth/`)
   - `login.php` → `pages/auth/login.php`
   - `register.php` → `pages/auth/register.php`
   - `logout.php` → `pages/auth/logout.php`

2. **Admin** (`pages/admin/`)
   - `admin.php` → `pages/admin/admin.php`

3. **Recipe** (`pages/recipe/`)
   - `add_recipe.php` → `pages/recipe/add_recipe.php`
   - `edit_recipe.php` → `pages/recipe/edit_recipe.php`
   - `recipe_detail.php` → `pages/recipe/recipe_detail.php`

4. **User** (`pages/user/`)
   - `profile.php` → `pages/user/profile.php`
   - `edit_profile.php` → `pages/user/edit_profile.php`

5. **Dokumentasi** (`docs/`)
   - `README.md` → `docs/README.md`
   - `CHANGELOG.md` → `docs/CHANGELOG.md`
   - `INSTALL.txt` → `docs/INSTALL.txt`

6. **Config**
   - `database.sql` → `config/database.sql`

## Path yang Perlu Diupdate

### 1. Update `require_once` di file yang dipindahkan

Untuk file di `pages/auth/`, `pages/admin/`, `pages/recipe/`, `pages/user/`:
```php
// Dari:
require_once 'config/functions.php';
require_once 'config/database.php';

// Menjadi:
require_once '../../config/functions.php';
require_once '../../config/database.php';
```

### 2. Update path CSS

```html
<!-- Dari: -->
<link rel="stylesheet" href="assets/css/style.css">

<!-- Menjadi: -->
<link rel="stylesheet" href="../../assets/css/style.css">
```

### 3. Update Link di Navbar (semua file)

File yang tetap di root (index.php, about.php, contact.php) - **TIDAK PERLU DIUBAH**

File di dalam `pages/*/` perlu update:
```php
// Link navigasi harus tambah ../../
<a href="../../index.php">Beranda</a>
<a href="../../about.php">Tentang</a>
<a href="../../contact.php">Kontak</a>
<a href="../admin/admin.php">Admin</a>
<a href="../recipe/add_recipe.php">Tambah Resep</a>
<a href="../user/profile.php">Profil</a>
<a href="../auth/logout.php">Keluar</a>
```

### 4. Update Form Action

```html
<!-- Di login.php dan register.php -->
<!-- Tidak perlu diubah karena form action ke dirinya sendiri -->

<!-- Di file lain yang ada form ke page yang dipindahkan -->
<form action="pages/auth/login.php">
<form action="pages/recipe/add_recipe.php">
```

### 5. Update Redirect di PHP

```php
// File di pages/*/ yang redirect ke root
header('Location: ../../index.php');

// File di pages/*/ yang redirect ke sesama subfolder
header('Location: admin.php');

// File di pages/*/ yang redirect ke subfolder lain
header('Location: ../recipe/recipe_detail.php?id=1');
```

### 6. Update include footer

```php
// Dari:
<?php include 'includes/footer.php'; ?>

// Menjadi:
<?php include '../../includes/footer.php'; ?>
```

## Alternatif: Gunakan Absolute Path

Untuk memudahkan, Anda bisa define base path di config/functions.php:

```php
// Di config/functions.php, tambahkan:
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/TugasAkhirPPKPI');

// Kemudian gunakan:
require_once BASE_PATH . '/config/functions.php';
```

## Update Link di File Root

File `index.php`, `about.php`, `contact.php` perlu update link ke file yang dipindahkan:

```php
<a href="pages/auth/login.php">Masuk</a>
<a href="pages/auth/register.php">Daftar</a>
<a href="pages/auth/logout.php">Keluar</a>
<a href="pages/admin/admin.php">Admin</a>
<a href="pages/recipe/add_recipe.php">Tambah Resep</a>
<a href="pages/recipe/recipe_detail.php?id=<?php echo $id; ?>">Detail</a>
<a href="pages/user/profile.php">Profil</a>
<a href="pages/user/edit_profile.php">Edit Profil</a>
```

## Catatan Penting

1. **API folder** tetap di root, tidak perlu diubah
2. **Uploads folder** tetap di root
3. File `.htaccess` mungkin perlu disesuaikan untuk URL rewriting
4. Pastikan test semua halaman setelah update
5. Update juga path di file JavaScript jika ada

## Rollback (jika diperlukan)

Jika ingin mengembalikan ke struktur lama, jalankan perintah berikut di PowerShell:

```powershell
# Pindahkan kembali semua file ke root
Move-Item -Path "pages\auth\*" -Destination "." -Force
Move-Item -Path "pages\admin\*" -Destination "." -Force
Move-Item -Path "pages\recipe\*" -Destination "." -Force
Move-Item -Path "pages\user\*" -Destination "." -Force
Move-Item -Path "docs\*" -Destination "." -Force
Move-Item -Path "config\database.sql" -Destination "." -Force

# Hapus folder yang kosong
Remove-Item -Path "pages" -Recurse -Force
Remove-Item -Path "docs" -Force
```
