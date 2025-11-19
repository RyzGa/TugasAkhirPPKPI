<?php
// Edit Profil
require_once '../../config/functions.php';
require_once '../../config/database.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $avatar = $user['avatar']; // Keep existing avatar by default
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
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['avatar_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Terjadi kesalahan saat mengunggah file.';
            } else {
                $maxSize = 2 * 1024 * 1024; // 2MB
                // Validasi ukuran file
                if ($file['size'] > $maxSize) {
                    $error = 'Ukuran file terlalu besar (maks 2MB).';
                } else {
                    $tmp = $file['tmp_name'];
                    $imgInfo = @getimagesize($tmp);
                    // Validasi apakah file adalah gambar
                    if ($imgInfo === false) {
                        $error = 'File bukan gambar yang valid.';
                    } else {
                        $mime = $imgInfo['mime'];
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp'
                        ];
                        // Validasi tipe gambar
                        if (!isset($allowed[$mime])) {
                            $error = 'Tipe gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
                        } else {
                            // Upload avatar baru ke Cloudinary
                            $cloudinaryResult = uploadToCloudinary($tmp, 'nusabites/avatars');

                            if ($cloudinaryResult) {
                                // Hapus avatar lama dari Cloudinary jika ada
                                if (!empty($user['avatar']) && strpos($user['avatar'], 'cloudinary.com') !== false) {
                                    // Extract public_id from URL
                                    preg_match('/\/([^\/]+)\.(jpg|png|webp)$/', $user['avatar'], $matches);
                                    if (isset($matches[1])) {
                                        deleteFromCloudinary('nusabites/avatars/' . $matches[1]);
                                    }
                                }

                                // Store Cloudinary URL
                                $avatar = $cloudinaryResult['secure_url'];
                            } else {
                                $error = 'Gagal mengupload gambar ke Cloudinary.';
                            }
                        }
                    }
                }
            }
        }

        if (empty($error)) {
            $conn = getDBConnection();

            // Keep avatar empty if no upload and no existing avatar
            // No automatic avatar generation

            // Query: Cek apakah email sudah digunakan user lain
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->bind_param("si", $email, $user['id']);
            $checkStmt->execute();
            $checkRes = $checkStmt->get_result();

            if ($checkRes->num_rows > 0) {
                $error = 'Email sudah digunakan oleh akun lain.';
            } else {
                // Query: Cek apakah username sudah digunakan user lain
                $checkNameStmt = $conn->prepare("SELECT id FROM users WHERE name = ? AND id != ?");
                $checkNameStmt->bind_param("si", $name, $user['id']);
                $checkNameStmt->execute();
                $checkNameRes = $checkNameStmt->get_result();

                if ($checkNameRes->num_rows > 0) {
                    $error = 'Username sudah digunakan oleh akun lain. Pilih username lain!';
                } else {
                    // Query: UPDATE data user di database
                    // Jika password diisi, update juga password (di-hash)
                    if (!empty($password)) {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, avatar = ?, password = ? WHERE id = ?");
                        $stmt->bind_param("ssssi", $name, $email, $avatar, $hashed, $user['id']);
                    } else {
                        // Jika password kosong, update tanpa password
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
                            $oldFull = dirname(dirname(__DIR__)) . '/' . $oldAvatar;
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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="../../index.php" class="logo">
                <img src="../../assets/images/logo.png" alt="NusaBites Logo" style="height: 40px;">
            </a>
            <nav class="nav-links">
                <a href="../../index.php" class="<?php echo isActivePage('index.php'); ?>">Beranda</a>
                <a href="../recipe/add_recipe.php" class="<?php echo isActivePage('add_recipe.php'); ?>"><i class="fas fa-plus"></i> Tambah Resep</a>
                <div class="profile-dropdown">
                    <button class="user-profile-btn" onclick="toggleProfileDropdown(event)">
                        <?php
                        $navAvatar = $user['avatar'];
                        if (!empty($navAvatar) && strpos($navAvatar, 'http') !== 0) {
                            $navAvatar = '../../' . $navAvatar;
                        }
                        ?>
                        <?php if (!empty($navAvatar)): ?>
                            <img src="<?php echo htmlspecialchars($navAvatar); ?>"
                                alt="<?php echo htmlspecialchars($user['name']); ?>"
                                class="avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; margin-left: 0.3rem;"></i>
                    </button>
                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Lihat Profil</span>
                        </a>
                        <a href="../auth/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sign Out</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <div class="container" style="padding: 2rem 1rem; max-width: 800px;">
        <div class="card" style="padding: 2rem;">
            <h2>Edit Profil</h2>

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
                    <label for="avatar_file">Unggah Foto Profil (opsional, max 2MB)</label>
                    <input type="file" id="avatar_file" name="avatar_file" accept="image/png,image/jpeg,image/webp" class="form-input">
                    <small class="text-gray">Format: JPG, PNG, WEBP. Jika tidak diunggah, icon profil default akan ditampilkan.</small>
                    <?php if (!empty($user['avatar'])): ?>
                        <div style="margin-top: 0.5rem;">
                            <small class="text-gray">
                                <i class="fas fa-check-circle" style="color: var(--color-success);"></i>
                                Foto profil saat ini: <strong><?php echo basename($user['avatar']); ?></strong>
                            </small>
                        </div>
                    <?php endif; ?>
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

    <script src="../../assets/js/dropdown.js"></script>

    <?php if ($error): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>

    <?php if ($success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?php echo addslashes($success); ?>',
                confirmButtonColor: '#28a745',
                timer: 2000
            }).then(() => {
                window.location.href = 'profile.php';
            });
        </script>
    <?php endif; ?>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>