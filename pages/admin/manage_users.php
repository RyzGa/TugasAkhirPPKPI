<?php
// Admin - Manajemen User
require_once '../../config/functions.php';
require_once '../../config/database.php';

requireLogin();
requireAdmin();

$user = getCurrentUser();
$conn = getDBConnection();

// Handle delete user
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];

    // Tidak bisa hapus diri sendiri
    if ($userId !== $user['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            header("Location: manage_users.php?success=deleted");
            exit;
        }
    }
}

// Handle update user (username, email, password, role)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $userId = (int)$_POST['user_id'];
    $newName = sanitizeInput($_POST['name']);
    $newEmail = sanitizeInput($_POST['email']);
    $newPassword = $_POST['password'];
    $newRole = $_POST['role'];

    // Tidak bisa ubah diri sendiri
    if ($userId !== $user['id']) {
        // Validasi username unique (kecuali untuk user yang sama)
        $checkNameStmt = $conn->prepare("SELECT id FROM users WHERE name = ? AND id != ?");
        $checkNameStmt->bind_param("si", $newName, $userId);
        $checkNameStmt->execute();
        $checkNameResult = $checkNameStmt->get_result();

        if ($checkNameResult->num_rows > 0) {
            $message = 'Username sudah digunakan oleh user lain!';
            $type = 'error';
        } else {
            // Validasi email unique (kecuali untuk user yang sama)
            $checkEmailStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkEmailStmt->bind_param("si", $newEmail, $userId);
            $checkEmailStmt->execute();
            $checkEmailResult = $checkEmailStmt->get_result();

            if ($checkEmailResult->num_rows > 0) {
                $message = 'Email sudah digunakan oleh user lain!';
                $type = 'error';
            } else {
                // Update user data
                if (!empty($newPassword)) {
                    // Update dengan password baru
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $newName, $newEmail, $hashedPassword, $newRole, $userId);
                } else {
                    // Update tanpa ubah password
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $newName, $newEmail, $newRole, $userId);
                }

                if ($stmt->execute()) {
                    // Update author_name di semua resep user ini
                    $updateRecipes = $conn->prepare("UPDATE recipes SET author_name = ? WHERE author_id = ?");
                    $updateRecipes->bind_param("si", $newName, $userId);
                    $updateRecipes->execute();

                    // Update author_name di semua review user ini
                    $updateReviews = $conn->prepare("UPDATE reviews SET author_name = ? WHERE user_id = ?");
                    $updateReviews->bind_param("si", $newName, $userId);
                    $updateReviews->execute();

                    $message = 'Data user berhasil diupdate!';
                    $type = 'success';
                } else {
                    $message = 'Gagal mengupdate data user!';
                    $type = 'error';
                }
            }
        }
    }
}

// Get all users with their recipe count - urutkan dari yang paling lama
$usersStmt = $conn->prepare("SELECT u.*, 
                              (SELECT COUNT(*) FROM recipes WHERE author_id = u.id) as recipe_count,
                              (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as review_count
                              FROM users u 
                              ORDER BY u.created_at ASC");
$usersStmt->execute();
$users = $usersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Statistics
$statsQuery = "SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admins";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Admin Nusa Bites</title>
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
                <a href="../../index.php">Beranda</a>
                <a href="admin.php"><i class="fas fa-shield-alt"></i> Dashboard</a>
                <a href="approve_recipes.php"><i class="fas fa-check-circle"></i> Validasi Resep</a>
                <a href="manage_users.php" class="active"><i class="fas fa-users"></i> Kelola User</a>
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
                        <a href="../user/profile.php" class="dropdown-item">
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

    <div class="container" style="padding: 2rem 1rem;">
        <div style="margin-bottom: 2rem;">
            <h1><i class="fas fa-users"></i> Manajemen User</h1>
            <p class="text-gray">Kelola semua user yang terdaftar di sistem</p>
        </div>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-users" style="font-size: 2rem; color: #3b82f6; margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $stats['total_users']; ?></div>
                <div class="text-gray text-sm">Total User</div>
            </div>

            <div class="card" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-user-shield" style="font-size: 2rem; color: var(--color-primary); margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $stats['total_admins']; ?></div>
                <div class="text-gray text-sm">Total Admin</div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card" style="padding: 1.5rem;">
            <h2 style="margin-bottom: 1.5rem;">Daftar User Terdaftar</h2>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-bg-light); border-bottom: 2px solid var(--color-border);">
                            <th style="padding: 0.75rem; text-align: left;">No</th>
                            <th style="padding: 0.75rem; text-align: left;">Nama</th>
                            <th style="padding: 0.75rem; text-align: left;">Email</th>
                            <th style="padding: 0.75rem; text-align: center;">Role</th>
                            <th style="padding: 0.75rem; text-align: center;">Resep</th>
                            <th style="padding: 0.75rem; text-align: center;">Review</th>
                            <th style="padding: 0.75rem; text-align: center;">Terdaftar</th>
                            <th style="padding: 0.75rem; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($users as $userData):
                        ?>
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 0.75rem;"><?php echo $no++; ?></td>
                                <td style="padding: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <?php if (!empty($userData['avatar'])): ?>
                                            <img src="<?php echo htmlspecialchars($userData['avatar']); ?>"
                                                alt="<?php echo htmlspecialchars($userData['name']); ?>"
                                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle" style="font-size: 2.5rem; color: var(--color-text-gray);"></i>
                                        <?php endif; ?>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($userData['name']); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem;"><?php echo htmlspecialchars($userData['email']); ?></td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <?php if ($userData['role'] === 'admin'): ?>
                                        <span class="badge" style="background: var(--color-primary); color: white;">
                                            <i class="fas fa-shield-alt"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-user"></i> User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <?php echo $userData['recipe_count']; ?>
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <?php echo $userData['review_count']; ?>
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <?php echo date('d M Y', strtotime($userData['created_at'])); ?>
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <?php if ($userData['id'] !== $user['id']): ?>
                                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                            <button onclick="editUser(<?php echo $userData['id']; ?>, '<?php echo htmlspecialchars($userData['name']); ?>', '<?php echo htmlspecialchars($userData['email']); ?>', '<?php echo $userData['role']; ?>')"
                                                class="btn btn-sm"
                                                style="background: #10b981; color: white; padding: 0.25rem 0.75rem;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteUser(<?php echo $userData['id']; ?>, '<?php echo htmlspecialchars($userData['name']); ?>')"
                                                class="btn btn-sm"
                                                style="background: #ef4444; color: white; padding: 0.25rem 0.75rem; border: none; cursor: pointer;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray text-sm">
                                            <i class="fas fa-lock"></i> Anda
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .edit-form-group {
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .edit-form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
            letter-spacing: 0.01em;
        }

        .edit-form-required {
            color: #ef4444;
            margin-left: 2px;
        }

        .edit-form-input {
            width: 100% !important;
            padding: 0.75rem 1rem !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 0.5rem !important;
            font-size: 0.95rem !important;
            transition: all 0.2s ease !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }

        .edit-form-input:focus {
            border-color: #10b981 !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }

        .edit-form-input::placeholder {
            color: #9ca3af;
        }

        .edit-form-hint {
            display: block;
            margin-top: 0.375rem;
            color: #6b7280;
            font-size: 0.8125rem;
            font-style: italic;
        }

        .edit-form-icon {
            color: #10b981;
            margin-right: 0.5rem;
        }

        .swal2-html-container {
            padding: 1.5rem 1rem !important;
        }

        .swal2-title {
            color: #111827 !important;
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            padding: 1rem 0 !important;
        }
    </style>

    <script>
        function editUser(userId, name, email, currentRole) {
            Swal.fire({
                title: '✏️ Edit User',
                html: `
                    <div class="edit-form-group">
                        <label class="edit-form-label">
                            <i class="fas fa-user edit-form-icon"></i>Username
                            <span class="edit-form-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="edit-name" 
                            value="${name}" 
                            class="edit-form-input" 
                            placeholder="Masukkan username"
                            autocomplete="off">
                    </div>
                    
                    <div class="edit-form-group">
                        <label class="edit-form-label">
                            <i class="fas fa-envelope edit-form-icon"></i>Email
                            <span class="edit-form-required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="edit-email" 
                            value="${email}" 
                            class="edit-form-input" 
                            placeholder="contoh@email.com"
                            autocomplete="off">
                    </div>
                    
                    <div class="edit-form-group">
                        <label class="edit-form-label">
                            <i class="fas fa-lock edit-form-icon"></i>Password Baru
                        </label>
                        <input 
                            type="password" 
                            id="edit-password" 
                            class="edit-form-input" 
                            placeholder="••••••••"
                            autocomplete="new-password">
                        <small class="edit-form-hint">
                            <i class="fas fa-info-circle"></i> Kosongkan jika tidak ingin mengubah password (min. 6 karakter)
                        </small>
                    </div>
                    
                    <div class="edit-form-group">
                        <label class="edit-form-label">
                            <i class="fas fa-shield-alt edit-form-icon"></i>Role
                            <span class="edit-form-required">*</span>
                        </label>
                        <select id="edit-role" class="edit-form-input">
                            <option value="user" ${currentRole === 'user' ? 'selected' : ''}>👤 User - Pengguna Biasa</option>
                            <option value="admin" ${currentRole === 'admin' ? 'selected' : ''}>🛡️ Admin - Administrator</option>
                        </select>
                    </div>
                `,
                width: '550px',
                padding: '2rem',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-save"></i> Simpan Perubahan',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                buttonsStyling: true,
                customClass: {
                    confirmButton: 'swal2-confirm-custom',
                    cancelButton: 'swal2-cancel-custom'
                },
                preConfirm: () => {
                    const newName = document.getElementById('edit-name').value;
                    const newEmail = document.getElementById('edit-email').value;
                    const newPassword = document.getElementById('edit-password').value;
                    const role = document.getElementById('edit-role').value;

                    // Validasi
                    if (!newName || !newEmail) {
                        Swal.showValidationMessage('Username dan email harus diisi!');
                        return false;
                    }

                    if (!newEmail.includes('@')) {
                        Swal.showValidationMessage('Format email tidak valid!');
                        return false;
                    }

                    if (newPassword && newPassword.length < 6) {
                        Swal.showValidationMessage('Password minimal 6 karakter!');
                        return false;
                    }

                    return {
                        name: newName,
                        email: newEmail,
                        password: newPassword,
                        role: role
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="user_id" value="${userId}">
                        <input type="hidden" name="name" value="${result.value.name}">
                        <input type="hidden" name="email" value="${result.value.email}">
                        <input type="hidden" name="password" value="${result.value.password}">
                        <input type="hidden" name="role" value="${result.value.role}">
                        <input type="hidden" name="update_user" value="1">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function deleteUser(userId, name) {
            Swal.fire({
                title: 'Hapus User?',
                html: `Apakah Anda yakin ingin menghapus user <strong>${name}</strong>?<br><br><span style="color: #ef4444;">Semua resep dan review user ini akan ikut terhapus!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `manage_users.php?delete=1&id=${userId}`;
                }
            });
        }

        <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'User berhasil dihapus!',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'manage_users.php';
            });
        <?php endif; ?>

        <?php if (isset($message)): ?>
            Swal.fire({
                icon: '<?php echo $type; ?>',
                title: '<?php echo $type === "success" ? "Berhasil!" : "Gagal!"; ?>',
                text: '<?php echo $message; ?>',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'manage_users.php';
            });
        <?php endif; ?>
    </script>
    <script src="../../assets/js/dropdown.js"></script>
</body>

</html>