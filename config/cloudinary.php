<?php

/**
 * Cloudinary Configuration
 * 
 * Credentials dari Cloudinary Dashboard
 */

define('CLOUDINARY_CLOUD_NAME', 'di9ocdzxe');
define('CLOUDINARY_API_KEY', '448475687916756');
define('CLOUDINARY_API_SECRET', 'Lw9pKXtXc83IW0mkSH1YlrOhNt4');
define('CLOUDINARY_UPLOAD_PRESET', 'nusabites');     // Optional: buat upload preset di Cloudinary

/**
 * Upload file to Cloudinary
 * 
 * @param string $filePath Path to the local file
 * @param string $folder Folder in Cloudinary (e.g., 'avatars', 'recipes')
 * @return array|false Returns array with 'secure_url' and 'public_id' on success, false on failure
 */
function uploadToCloudinary($filePath, $folder = 'nusabites')
{
    $cloudName = CLOUDINARY_CLOUD_NAME;
    $apiKey = CLOUDINARY_API_KEY;
    $apiSecret = CLOUDINARY_API_SECRET;

    // Generate timestamp
    $timestamp = time();

    // Prepare upload parameters
    $params = [
        'file' => new CURLFile($filePath),
        'timestamp' => $timestamp,
        'folder' => $folder,
        'api_key' => $apiKey
    ];

    // Generate signature
    $signatureString = "folder={$folder}&timestamp={$timestamp}{$apiSecret}";
    $signature = hash('sha256', $signatureString);
    $params['signature'] = $signature;

    // Upload to Cloudinary
    $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return [
            'secure_url' => $result['secure_url'],
            'public_id' => $result['public_id']
        ];
    }

    return false;
}

/**
 * Delete file from Cloudinary
 * 
 * @param string $publicId Public ID of the file to delete
 * @return bool
 */
function deleteFromCloudinary($publicId)
{
    $cloudName = CLOUDINARY_CLOUD_NAME;
    $apiKey = CLOUDINARY_API_KEY;
    $apiSecret = CLOUDINARY_API_SECRET;

    $timestamp = time();

    // Generate signature
    $signatureString = "public_id={$publicId}&timestamp={$timestamp}{$apiSecret}";
    $signature = hash('sha256', $signatureString);

    $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";

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

/**
 * Transform Cloudinary URL with width/height
 * 
 * @param string $url Cloudinary URL
 * @param int $width Width in pixels
 * @param int $height Height in pixels
 * @return string Transformed URL
 */
function transformCloudinaryUrl($url, $width = null, $height = null, $crop = 'fill')
{
    if (strpos($url, 'cloudinary.com') === false) {
        return $url;
    }

    $transformations = [];
    if ($width) $transformations[] = "w_{$width}";
    if ($height) $transformations[] = "h_{$height}";
    if ($width || $height) $transformations[] = "c_{$crop}";

    if (empty($transformations)) {
        return $url;
    }

    $transform = implode(',', $transformations);
    return str_replace('/upload/', "/upload/{$transform}/", $url);
}
