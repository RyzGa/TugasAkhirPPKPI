<?php

/**
 * Script Migrasi Gambar Resep Lokal ke Cloudinary
 * 
 * Script ini akan:
 * 1. Upload semua gambar resep dari uploads/recipes/ ke Cloudinary
 * 2. Update database recipes dengan URL Cloudinary baru
 * 
 * PENTING: Backup database dulu sebelum menjalankan script ini!
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/cloudinary.php';

echo "=== MIGRASI GAMBAR RESEP KE CLOUDINARY ===\n\n";

// Cek koneksi database
$conn = getDBConnection();
if (!$conn) {
    die("Error: Koneksi database gagal!\n");
}

// Get all recipes dengan gambar lokal
$query = "SELECT id, title, image FROM recipes WHERE image != '' AND image NOT LIKE 'http%'";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo "Tidak ada gambar resep lokal yang perlu dimigrasi.\n";
} else {
    echo "Ditemukan " . $result->num_rows . " gambar resep yang akan dimigrasi...\n\n";

    $success = 0;
    $failed = 0;

    while ($recipe = $result->fetch_assoc()) {
        echo "Processing recipe #{$recipe['id']} - {$recipe['title']}...\n";
        echo "  Gambar lokal: {$recipe['image']}\n";

        $localPath = __DIR__ . '/' . $recipe['image'];

        // Cek apakah file exists
        if (!file_exists($localPath)) {
            echo "  ❌ File tidak ditemukan: {$localPath}\n";
            $failed++;
            continue;
        }

        // Upload to Cloudinary
        echo "  Uploading ke Cloudinary...\n";
        $cloudinaryResult = uploadToCloudinary($localPath, 'nusabites/recipes');

        if ($cloudinaryResult) {
            $cloudinaryUrl = $cloudinaryResult['secure_url'];
            echo "  ✅ Upload berhasil: {$cloudinaryUrl}\n";

            // Update database
            $updateStmt = $conn->prepare("UPDATE recipes SET image = ? WHERE id = ?");
            $updateStmt->bind_param("si", $cloudinaryUrl, $recipe['id']);

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
echo "Folder: nusabites/recipes\n";
