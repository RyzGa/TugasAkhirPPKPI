<?php

/**
 * Script Migrasi Gambar Lokal ke Cloudinary
 * 
 * Script ini akan:
 * 1. Upload semua foto profil dari uploads/avatars/ ke Cloudinary
 * 2. Update database users dengan URL Cloudinary baru
 * 
 * PENTING: Backup database dulu sebelum menjalankan script ini!
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/cloudinary.php';

echo "=== MIGRASI AVATARS KE CLOUDINARY ===\n\n";

// Cek koneksi database
$conn = getDBConnection();
if (!$conn) {
    die("Error: Koneksi database gagal!\n");
}

// Get all users dengan avatar lokal
$query = "SELECT id, name, avatar FROM users WHERE avatar != '' AND avatar NOT LIKE 'http%'";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo "Tidak ada avatar lokal yang perlu dimigrasi.\n";
} else {
    echo "Ditemukan " . $result->num_rows . " avatar yang akan dimigrasi...\n\n";

    $success = 0;
    $failed = 0;

    while ($user = $result->fetch_assoc()) {
        echo "Processing user #{$user['id']} - {$user['name']}...\n";
        echo "  Avatar lokal: {$user['avatar']}\n";

        $localPath = __DIR__ . '/' . $user['avatar'];

        // Cek apakah file exists
        if (!file_exists($localPath)) {
            echo "  ❌ File tidak ditemukan: {$localPath}\n";
            $failed++;
            continue;
        }

        // Upload to Cloudinary
        echo "  Uploading ke Cloudinary...\n";
        $cloudinaryResult = uploadToCloudinary($localPath, 'nusabites/avatars');

        if ($cloudinaryResult) {
            $cloudinaryUrl = $cloudinaryResult['secure_url'];
            echo "  ✅ Upload berhasil: {$cloudinaryUrl}\n";

            // Update database
            $updateStmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $updateStmt->bind_param("si", $cloudinaryUrl, $user['id']);

            if ($updateStmt->execute()) {
                echo "  ✅ Database updated\n";

                // Hapus file lokal (opsional - comment jika ingin keep backup)
                // unlink($localPath);
                // echo "  ✅ File lokal dihapus\n";

                $success++;
            } else {
                echo "  ❌ Gagal update database: " . $updateStmt->error . "\n";
                $failed++;
            }
        } else {
            echo "  ❌ Upload ke Cloudinary gagal\n";
            $failed++;
        }

        echo "\n";
    }

    echo "\n=== RINGKASAN ===\n";
    echo "Berhasil: {$success}\n";
    echo "Gagal: {$failed}\n";
}

closeDBConnection($conn);

echo "\n=== SELESAI ===\n";
echo "Cek di Cloudinary Media Library: https://cloudinary.com/console/media_library\n";
echo "Folder: nusabites/avatars\n";
