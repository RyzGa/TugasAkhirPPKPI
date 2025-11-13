<?php
require_once 'config/functions.php';
require_once 'config/database.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $avatar = sanitizeInput($_POST['avatar']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($name) || empty($email)) {
        $error = 'Nama dan email harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!empty($password) && $password !== $confirm) {
        $error = 'Password dan konfirmasi tidak cocok.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Handle file upload if provided
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['avatar_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Terjadi kesalahan saat mengunggah file.';
            } else {
                $maxSize = 2 * 1024 * 1024; // 2MB
                if ($file['size'] > $maxSize) {
                    $error = 'Ukuran file terlalu besar (maks 2MB).';
                } else {
                    $tmp = $file['tmp_name'];
                    $imgInfo = @getimagesize($tmp);
                    if ($imgInfo === false) {
                        $error = 'File bukan gambar yang valid.';
                    } else {
                        $mime = $imgInfo['mime'];
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp'
                        ];
                        if (!isset($allowed[$mime])) {
                            $error = 'Tipe gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
                        } else {
                            $ext = $allowed[$mime];
                            $uploadDir = __DIR__ . '/uploads/avatars';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            try {
                                $random = bin2hex(random_bytes(6));
                            } catch (Exception $e) {
                                $random = time();
                            }
                            $filename = time() . '_' . $random . '.' . $ext;
                            $dest = $uploadDir . '/' . $filename;
                            if (move_uploaded_file($tmp, $dest)) {
                                // Resize the saved image to limit dimensions
                                $webPath = 'uploads/avatars/' . $filename;
                                $fullPath = __DIR__ . '/' . $webPath;
                                // Attempt resize; if failed, keep original
                                if (function_exists('resizeImage')) {
                                    @resizeImage($fullPath, $fullPath, 400, 400);
                                }
                                // Store web-accessible path
                                $avatar = $webPath;
                            } else {
                                $error = 'Gagal menyimpan file gambar.';
                            }
                        }
                    }
                }
            }
        }

        if (empty($error)) {
            $conn = getDBConnection();

            // If avatar URL is empty, generate a default based on name
            if (empty($avatar)) {
                $avatar = generateAvatarUrl($name);
            }

            // Check email uniqueness (exclude current user)
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->bind_param("si", $email, $user['id']);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes->num_rows > 0) {
                $error = 'Email sudah digunakan oleh akun lain.';
            } else {
                // Build update query
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, avatar = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $name, $email, $avatar, $hashed, $user['id']);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $name, $email, $avatar, $user['id']);
                }

                if ($stmt->execute()) {
                    // Update session data
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_avatar'] = $avatar;

                    // Delete previous avatar file if it was a local upload
                    $oldAvatar = isset($user['avatar']) ? $user['avatar'] : '';
                    if (!empty($oldAvatar) && strpos($oldAvatar, 'uploads/avatars/') === 0 && $oldAvatar !== $avatar) {
                        $oldFull = __DIR__ . '/' . $oldAvatar;
                        if (is_file($oldFull)) {
                            @unlink($oldFull);
                        }
                    }

                    $success = 'Profil berhasil diperbarui.';
                    // Refresh local $user
                    $user = getCurrentUser();
                    // Redirect back to profile page
                    header('Location: profile.php');
                    exit;
                } else {
                    $error = 'Terjadi kesalahan saat menyimpan. Silakan coba lagi.';
                }

                $stmt->close();
            }

            closeDBConnection($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Nusa Bites</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-hat-chef" style="font-size: 1.5rem;"></i>
                </div>
                <span>Nusa Bites</span>
            </a>
            <nav class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="add_recipe.php"><i class="fas fa-plus"></i> Tambah Resep</a>
                <a href="profile.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['name']); ?></a>
                <a href="logout.php">Keluar</a>
            </nav>
        </div>
    </header>

    <div class="container" style="padding: 2rem 1rem; max-width: 800px;">
        <div class="card" style="padding: 2rem;">
            <h2>Edit Profil</h2>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_profile.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-input" required value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label for="avatar">URL Foto Profil</label>
                    <input type="text" id="avatar" name="avatar" class="form-input" placeholder="https://example.com/avatar.jpg" value="<?php echo htmlspecialchars($user['avatar']); ?>">
                    <small class="text-gray">Masukkan URL gambar atau kosongkan untuk menggunakan avatar default.</small>
                </div>

                <div class="form-group">
                    <label for="avatar_file">Unggah Foto Profil (opsional, max 2MB)</label>
                    <input type="file" id="avatar_file" name="avatar_file" accept="image/png,image/jpeg,image/webp" class="form-input">
                    <small class="text-gray">Jika Anda mengunggah gambar, itu akan menggantikan URL avatar.</small>
                </div>

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--color-border);">
                    <h4>Ubah Password (opsional)</h4>
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Kosongkan jika tidak ingin mengganti">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Ulangi password">
                    </div>
                </div>

                <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="profile.php" class="btn">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>