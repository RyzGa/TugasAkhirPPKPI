<?php
// Set session configuration SEBELUM session_start()
ini_set('session.cookie_lifetime', 0); // 0 = hilang saat browser ditutup
ini_set('session.gc_maxlifetime', 1800); // Session timeout 30 menit

session_start();

// Session timeout: 30 menit inactivity (1800 detik)
$timeout_duration = 1800;

// Cek apakah session sudah timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session expired, destroy dan redirect ke login
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['session_expired'] = true;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Regenerate session ID untuk keamanan (hanya sekali setelah login)
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}

require_once 'database.php';
require_once 'cloudinary.php';

// Fungsi untuk mengecek apakah user sudah login -->
// Mengembalikan true jika session user_id ada -->
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mengecek apakah user adalah admin -->
// Mengembalikan true jika role user adalah 'admin' -->
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fungsi untuk mendapatkan data user yang sedang login -->
// Mengembalikan array berisi id, name, email, role, avatar atau null jika belum login -->
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'avatar' => $_SESSION['user_avatar']
    ];
}

// Fungsi untuk memastikan user sudah login -->
// Redirect ke login.php jika belum login -->
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Fungsi untuk memastikan user adalah admin -->
// Redirect ke index.php jika bukan admin -->
function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Fungsi untuk membersihkan input dari user -->
// Menghapus spasi, backslash, dan mengkonversi karakter khusus ke HTML entities -->
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk generate URL avatar - deprecated -->
// Return string kosong, icon default akan ditampilkan -->
function generateAvatarUrl($seed)
{
    return ""; // Return empty string, icon will be shown instead
}

// Fungsi untuk resize gambar -->
// Mengubah ukuran gambar sesuai max width/height dengan menjaga aspect ratio -->
// Parameter: path sumber, path tujuan, max width (default 400px), max height (default 400px) -->
function resizeImage($sourcePath, $destPath, $maxWidth = 400, $maxHeight = 400)
{
    // Cek apakah file sumber ada
    if (!file_exists($sourcePath)) {
        return false;
    }

    // Ambil informasi gambar (width, height, mime type)
    $info = @getimagesize($sourcePath);
    if ($info === false) {
        return false;
    }

    list($width, $height) = $info;
    $mime = $info['mime'];

    // Hitung ukuran baru dengan menjaga aspect ratio
    $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    // Jika ukuran sudah sesuai, tidak perlu resize
    if ($newWidth === $width && $newHeight === $height) {
        if ($sourcePath !== $destPath) {
            return copy($sourcePath, $destPath);
        }
        return true;
    }

    // Buat image resource berdasarkan tipe file
    switch ($mime) {
        case 'image/jpeg':
            if (function_exists('imagecreatefromjpeg')) {
                $srcImg = imagecreatefromjpeg($sourcePath);
            } else {
                // GD library tidak tersedia, copy tanpa resize
                return copy($sourcePath, $destPath);
            }
            break;
        case 'image/png':
            if (function_exists('imagecreatefrompng')) {
                $srcImg = imagecreatefrompng($sourcePath);
            } else {
                // GD library tidak tersedia, copy tanpa resize
                return copy($sourcePath, $destPath);
            }
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $srcImg = imagecreatefromwebp($sourcePath);
            } else {
                // WebP tidak didukung, copy tanpa resize
                return copy($sourcePath, $destPath);
            }
            break;
        default:
            return false;
    }

    if (!$srcImg) {
        return false;
    }

    // Buat canvas baru dengan ukuran yang telah dihitung
    $dstImg = imagecreatetruecolor($newWidth, $newHeight);

    // Pertahankan transparansi untuk PNG dan WEBP
    if (in_array($mime, ['image/png', 'image/webp'])) {
        imagealphablending($dstImg, false);
        imagesavealpha($dstImg, true);
        $transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
        imagefilledrectangle($dstImg, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Salin dan resize gambar ke canvas baru
    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Simpan gambar hasil resize sesuai format
    $saved = false;
    switch ($mime) {
        case 'image/jpeg':
            $saved = imagejpeg($dstImg, $destPath, 85); // Quality 85%
            break;
        case 'image/png':
            $saved = imagepng($dstImg, $destPath, 6); // Compression level 6
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                $saved = imagewebp($dstImg, $destPath, 85); // Quality 85%
            }
            break;
    }

    // Hapus resource dari memory
    imagedestroy($srcImg);
    imagedestroy($dstImg);

    return $saved;
}

// Fungsi untuk mengecek apakah halaman saat ini aktif -->
// Return 'active' jika nama halaman sesuai, untuk styling navbar -->
function isActivePage($pageName)
{
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $pageName ? 'active' : '';
}
