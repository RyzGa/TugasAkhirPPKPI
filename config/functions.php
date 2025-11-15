<?php
session_start();
require_once 'database.php';

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get current user data
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

// Require login
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Require admin
function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Sanitize input
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate avatar URL - returns empty string (no longer using generated avatars)
function generateAvatarUrl($seed)
{
    return ""; // Return empty string, icon will be shown instead
}

// Resize image to fit within max width/height while preserving aspect ratio.
function resizeImage($sourcePath, $destPath, $maxWidth = 400, $maxHeight = 400)
{
    if (!file_exists($sourcePath)) {
        return false;
    }

    $info = @getimagesize($sourcePath);
    if ($info === false) {
        return false;
    }

    list($width, $height) = $info;
    $mime = $info['mime'];

    // Calculate new size
    $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);

    if ($newWidth === $width && $newHeight === $height) {
        // No resize needed, but ensure we can still copy if dest differs
        if ($sourcePath !== $destPath) {
            return copy($sourcePath, $destPath);
        }
        return true;
    }

    switch ($mime) {
        case 'image/jpeg':
            if (function_exists('imagecreatefromjpeg')) {
                $srcImg = imagecreatefromjpeg($sourcePath);
            } else {
                // GD not available; fallback to copying file (no resize)
                return copy($sourcePath, $destPath);
            }
            break;
        case 'image/png':
            if (function_exists('imagecreatefrompng')) {
                $srcImg = imagecreatefrompng($sourcePath);
            } else {
                // GD not available; fallback to copying file (no resize)
                return copy($sourcePath, $destPath);
            }
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $srcImg = imagecreatefromwebp($sourcePath);
            } else {
                // GD/webp support not available; fallback to copying
                return copy($sourcePath, $destPath);
            }
            break;
        default:
            return false;
    }

    if (!$srcImg) {
        return false;
    }

    $dstImg = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG and WEBP
    if (in_array($mime, ['image/png', 'image/webp'])) {
        imagealphablending($dstImg, false);
        imagesavealpha($dstImg, true);
        $transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
        imagefilledrectangle($dstImg, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $saved = false;
    switch ($mime) {
        case 'image/jpeg':
            $saved = imagejpeg($dstImg, $destPath, 85);
            break;
        case 'image/png':
            // quality: 0 (no compression) - 9
            $saved = imagepng($dstImg, $destPath, 6);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                $saved = imagewebp($dstImg, $destPath, 85);
            }
            break;
    }

    imagedestroy($srcImg);
    imagedestroy($dstImg);

    return $saved;
}

// Check if current page is active
function isActivePage($pageName)
{
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $pageName ? 'active' : '';
}
