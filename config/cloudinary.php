<?php

// Konfigurasi Cloudinary untuk cloud storage gambar -->
// Credentials didapat dari Cloudinary Dashboard -->

define('CLOUDINARY_CLOUD_NAME', 'di9ocdzxe');           // Nama cloud Cloudinary
define('CLOUDINARY_API_KEY', '448475687916756');        // API Key untuk autentikasi
define('CLOUDINARY_API_SECRET', 'Lw9pKXtXc83IW0mkSH1YlrOhNt4');  // API Secret
define('CLOUDINARY_UPLOAD_PRESET', 'nusabites');        // Upload preset (optional)

// Fungsi untuk upload file ke Cloudinary -->
// Parameter: path file lokal, nama folder di Cloudinary -->
// Return: array dengan 'secure_url' dan 'public_id' jika sukses, false jika gagal -->
function uploadToCloudinary($filePath, $folder = 'nusabites')
{
    $cloudName = CLOUDINARY_CLOUD_NAME;
    $apiKey = CLOUDINARY_API_KEY;
    $apiSecret = CLOUDINARY_API_SECRET;

    // Generate timestamp untuk signature
    $timestamp = time();

    // Siapkan parameter upload
    $params = [
        'file' => new CURLFile($filePath),
        'timestamp' => $timestamp,
        'folder' => $folder,
        'api_key' => $apiKey
    ];

    // Generate signature untuk keamanan API
    $signatureString = "folder={$folder}&timestamp={$timestamp}{$apiSecret}";
    $signature = hash('sha256', $signatureString);
    $params['signature'] = $signature;

    // URL endpoint Cloudinary API
    $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

    // Kirim request menggunakan cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Parse response jika sukses
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return [
            'secure_url' => $result['secure_url'],   // URL HTTPS gambar
            'public_id' => $result['public_id']      // ID untuk delete/transform
        ];
    }

    return false;
}

// Fungsi untuk menghapus file dari Cloudinary -->
// Parameter: public_id dari file yang akan dihapus -->
// Return: true jika sukses, false jika gagal -->
function deleteFromCloudinary($publicId)
{
    $cloudName = CLOUDINARY_CLOUD_NAME;
    $apiKey = CLOUDINARY_API_KEY;
    $apiSecret = CLOUDINARY_API_SECRET;

    $timestamp = time();

    // Generate signature untuk autentikasi
    $signatureString = "public_id={$publicId}&timestamp={$timestamp}{$apiSecret}";
    $signature = hash('sha256', $signatureString);

    // URL endpoint untuk delete
    $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";

    // Kirim request delete menggunakan cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'public_id' => $publicId,
        'signature' => $signature,
        'api_key' => $apiKey,
        'timestamp' => $timestamp
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}

// Fungsi untuk transform URL Cloudinary (resize, crop, dll) -->
// Parameter: URL Cloudinary, width, height, crop mode -->
// Return: URL yang sudah ditransform -->
function transformCloudinaryUrl($url, $width = null, $height = null, $crop = 'fill')
{
    // Cek apakah URL dari Cloudinary
    if (strpos($url, 'cloudinary.com') === false) {
        return $url;
    }

    // Build transformation parameters
    $transformations = [];
    if ($width) $transformations[] = "w_{$width}";      // Width
    if ($height) $transformations[] = "h_{$height}";    // Height
    if ($width || $height) $transformations[] = "c_{$crop}";  // Crop mode

    if (empty($transformations)) {
        return $url;
    }

    // Inject transformations ke URL
    $transform = implode(',', $transformations);
    return str_replace('/upload/', "/upload/{$transform}/", $url);
}
