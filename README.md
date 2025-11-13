# Nusa Bites - Platform Resep Masakan Nusantara

Website resep masakan nusantara yang dibangun dengan PHP, CSS, dan MySQL. Project ini adalah konversi dari React/TypeScript ke PHP murni dengan mempertahankan tampilan dan fungsionalitas yang sama.

## 🌟 Fitur

- ✅ **Jelajah Resep** - Temukan ribuan resep masakan dari berbagai daerah di Indonesia
- ✅ **Filter & Pencarian** - Filter berdasarkan kategori, region, waktu memasak, dan rating
- ✅ **Authentication** - Sistem login dan registrasi user
- ✅ **Tambah & Edit Resep** - User dapat menambahkan dan mengedit resep mereka sendiri
- ✅ **Review & Rating** - Berikan review dan rating untuk resep
- ✅ **Favorit** - Simpan resep favorit Anda
- ✅ **User Profile** - Kelola resep dan favorit Anda
- ✅ **Admin Dashboard** - Admin dapat mengelola semua resep
- ✅ **Responsive Design** - Tampilan optimal di berbagai perangkat

## 🛠️ Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Icons**: Font Awesome 6.4.0

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache/Nginx Web Server
- XAMPP/WAMP/LAMP (recommended)

## 🚀 Instalasi

### 1. Clone atau Download Project

```bash
cd C:\xampp\htdocs
# atau download dan ekstrak di folder htdocs
```

### 2. Buat Database

1. Buka **phpMyAdmin** (http://localhost/phpmyadmin)
2. Klik tab **SQL**
3. Copy semua isi file `database.sql`
4. Paste di SQL editor dan klik **Go**

Atau melalui command line:

```bash
mysql -u root -p < database.sql
```

### 3. Konfigurasi Database

Edit file `config/database.php` jika perlu menyesuaikan kredensial database:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'nusabites');
```

### 4. Jalankan Website

1. Pastikan Apache dan MySQL sudah running di XAMPP
2. Buka browser dan akses: http://localhost/NusaBites

## 👤 Default Accounts

Setelah import database, Anda dapat login dengan akun berikut:

**Admin Account:**
- Email: `admin@nusabites.com`
- Password: `password`

**User Account:**
- Email: `siti@example.com`
- Password: `password`

## 📁 Struktur Project

```
NusaBites/
├── api/                      # API endpoints untuk AJAX
│   ├── toggle_like.php       # Handle like/unlike resep
│   └── delete_recipe.php     # Delete resep
├── assets/                   # Asset files
│   └── css/
│       └── style.css         # Main stylesheet
├── config/                   # Configuration files
│   ├── database.php          # Database connection
│   └── functions.php         # Helper functions
├── index.php                 # Homepage - daftar resep
├── login.php                 # Halaman login
├── register.php              # Halaman registrasi
├── logout.php                # Logout handler
├── recipe_detail.php         # Detail resep
├── add_recipe.php            # Tambah resep baru
├── edit_recipe.php           # Edit resep
├── profile.php               # User profile
├── admin.php                 # Admin dashboard
├── about.php                 # Tentang kami
├── contact.php               # Kontak
├── database.sql              # Database schema & data
└── README.md                 # Dokumentasi
```

## 🎨 Fitur Utama

### 1. Homepage (index.php)
- Menampilkan semua resep dalam grid layout
- Filter sidebar (kategori, region, rating)
- Search functionality
- Responsive grid layout

### 2. Recipe Detail (recipe_detail.php)
- Informasi lengkap resep
- Bahan-bahan dan langkah memasak
- Review dan rating system
- Like/unlike functionality

### 3. User Management
- Login & Register
- Profile management
- My recipes & favorites
- Session-based authentication

### 4. Recipe Management
- Add new recipe
- Edit recipe (owner & admin)
- Delete recipe (owner & admin)
- JSON storage untuk ingredients & steps

### 5. Review System
- Add review dengan rating (1-5 stars)
- Auto-calculate average rating
- Display review list

## 🔧 Troubleshooting

### Error "Cannot connect to database"
- Pastikan MySQL service running
- Cek kredensial di `config/database.php`
- Pastikan database `nusabites` sudah dibuat

### Error "Call to undefined function"
- Pastikan file `config/functions.php` di-include
- Cek PHP version (minimal 7.4)

### CSS tidak muncul
- Pastikan path ke `assets/css/style.css` benar
- Clear browser cache

### Gambar tidak muncul
- Pastikan URL gambar valid
- Fallback ke placeholder jika error

## 📝 Catatan Pengembangan

### Konversi dari React ke PHP

Project ini dikonversi dari React/TypeScript + Vite ke PHP murni dengan:
- Tailwind CSS → Custom CSS (style.css)
- React Components → PHP includes/functions
- useState/useEffect → PHP sessions & database queries
- React Router → PHP page navigation
- TypeScript interfaces → PHP arrays/objects

### Database Design

Menggunakan 4 tabel utama:
1. **users** - Data user (admin & regular user)
2. **recipes** - Data resep dengan JSON untuk ingredients & steps
3. **reviews** - Review dan rating dari user
4. **liked_recipes** - Relasi many-to-many untuk favorite

### Security Features

- Password hashing dengan `password_hash()`
- SQL injection prevention dengan prepared statements
- XSS prevention dengan `htmlspecialchars()`
- Session-based authentication
- CSRF protection (bisa ditambahkan)

## 🚧 Pengembangan Selanjutnya

Fitur yang bisa ditambahkan:
- [ ] Upload gambar ke server (saat ini pakai URL)
- [ ] Pagination untuk list resep
- [ ] Advanced search (by ingredients)
- [ ] Social sharing
- [ ] Email verification
- [ ] Forgot password
- [ ] Recipe categories management
- [ ] User roles & permissions
- [ ] API documentation

## 📄 License

This project is open source and available for educational purposes.

## 👨‍💻 Developer

Converted from React/TypeScript to PHP by GitHub Copilot

---

**Selamat mencoba! Jika ada pertanyaan, silakan buka issue di GitHub.**
