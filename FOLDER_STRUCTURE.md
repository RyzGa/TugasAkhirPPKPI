# Struktur Folder Nusa Bites

## Struktur Direktori

```
TugasAkhirPPKPI/
├── index.php              # Halaman utama (home/beranda)
├── about.php              # Halaman tentang
├── contact.php            # Halaman kontak
│
├── pages/                 # Semua halaman aplikasi
│   ├── auth/             # Halaman autentikasi
│   │   ├── login.php     # Halaman login
│   │   ├── register.php  # Halaman registrasi
│   │   └── logout.php    # Proses logout
│   │
│   ├── admin/            # Halaman admin
│   │   └── admin.php     # Dashboard admin
│   │
│   ├── recipe/           # Halaman resep
│   │   ├── add_recipe.php      # Tambah resep
│   │   ├── edit_recipe.php     # Edit resep
│   │   └── recipe_detail.php   # Detail resep
│   │
│   └── user/             # Halaman user
│       ├── profile.php         # Profil user
│       └── edit_profile.php    # Edit profil
│
├── api/                  # API endpoints
│   ├── delete_recipe.php
│   └── toggle_like.php
│
├── assets/               # Asset statis
│   └── css/
│       └── style.css     # File CSS utama
│
├── config/               # Konfigurasi
│   ├── database.php      # Koneksi database
│   ├── functions.php     # Fungsi helper
│   └── database.sql      # Schema database
│
├── includes/             # File yang di-include
│   └── footer.php        # Footer template
│
├── uploads/              # File upload user
│   └── avatars/          # Avatar user
│
└── docs/                 # Dokumentasi
    ├── README.md         # Dokumentasi utama
    ├── CHANGELOG.md      # Log perubahan
    └── INSTALL.txt       # Instruksi instalasi
```

## Penjelasan Folder

### `/pages/`
Berisi semua halaman aplikasi yang dikelompokkan berdasarkan fungsi:
- **auth/** - Halaman login, register, dan logout
- **admin/** - Halaman khusus admin
- **recipe/** - Halaman terkait resep (tambah, edit, detail)
- **user/** - Halaman profil user

### `/api/`
Berisi endpoint API untuk operasi AJAX/fetch seperti delete, like, dll.

### `/assets/`
Berisi file statis seperti CSS, JavaScript, dan gambar.

### `/config/`
Berisi file konfigurasi database, fungsi helper, dan schema database.

### `/includes/`
Berisi file template yang di-include seperti header, footer, navbar.

### `/uploads/`
Folder untuk menyimpan file upload dari user (avatar, gambar resep).

### `/docs/`
Berisi dokumentasi proyek.

## Update Path

Setelah reorganisasi folder, pastikan untuk update path di:
1. Link navigasi di navbar
2. Form action URL
3. Include/require statement
4. Redirect URL
