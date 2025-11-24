<?php
// Admin - Validasi Resep
require_once '../../config/functions.php';
require_once '../../config/database.php';

requireLogin();
requireAdmin();

$user = getCurrentUser();
$conn = getDBConnection();

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipeId = (int)$_POST['recipe_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE recipes SET status = 'approved', rejection_reason = NULL WHERE id = ?");
        $stmt->bind_param("i", $recipeId);
        $stmt->execute();
        $message = 'Resep berhasil disetujui!';
        $type = 'success';
    } elseif ($action === 'reject') {
        $reason = sanitizeInput($_POST['rejection_reason']);
        $stmt = $conn->prepare("UPDATE recipes SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->bind_param("si", $reason, $recipeId);
        $stmt->execute();
        $message = 'Resep berhasil ditolak!';
        $type = 'success';
    }
}

// Get filter status from URL
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Validate filter status
if (!in_array($filterStatus, ['pending', 'approved', 'rejected'])) {
    $filterStatus = 'pending';
}

// Get recipes based on filter
$recipesStmt = $conn->prepare("SELECT * FROM recipes WHERE status = ? ORDER BY created_at DESC");
$recipesStmt->bind_param("s", $filterStatus);
$recipesStmt->execute();
$recipes = $recipesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get counts for all statuses
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM recipes WHERE status = 'pending'")->fetch_assoc()['count'];
$approvedCount = $conn->query("SELECT COUNT(*) as count FROM recipes WHERE status = 'approved'")->fetch_assoc()['count'];
$rejectedCount = $conn->query("SELECT COUNT(*) as count FROM recipes WHERE status = 'rejected'")->fetch_assoc()['count'];

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Resep - Admin Nusa Bites</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Hover effect for clickable status cards */
        .card[href]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
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
                <a href="approve_recipes.php" class="active"><i class="fas fa-check-circle"></i> Validasi Resep</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Kelola User</a>
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
            <h1><i class="fas fa-check-circle"></i> Validasi Resep</h1>
            <p class="text-gray">Setujui atau tolak resep yang dikirim oleh user</p>
        </div>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
            <a href="?status=pending" class="card" style="padding: 1.5rem; text-align: center; text-decoration: none; transition: all 0.3s; <?php echo $filterStatus === 'pending' ? 'border: 2px solid #f59e0b; box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);' : 'border: 1px solid #e5e7eb;'; ?>">
                <i class="fas fa-clock" style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $pendingCount; ?></div>
                <div class="text-gray text-sm">Menunggu Validasi</div>
            </a>

            <a href="?status=approved" class="card" style="padding: 1.5rem; text-align: center; text-decoration: none; transition: all 0.3s; <?php echo $filterStatus === 'approved' ? 'border: 2px solid #10b981; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);' : 'border: 1px solid #e5e7eb;'; ?>">
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $approvedCount; ?></div>
                <div class="text-gray text-sm">Disetujui</div>
            </a>

            <a href="?status=rejected" class="card" style="padding: 1.5rem; text-align: center; text-decoration: none; transition: all 0.3s; <?php echo $filterStatus === 'rejected' ? 'border: 2px solid #ef4444; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);' : 'border: 1px solid #e5e7eb;'; ?>">
                <i class="fas fa-times-circle" style="font-size: 2rem; color: #ef4444; margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $rejectedCount; ?></div>
                <div class="text-gray text-sm">Ditolak</div>
            </a>
        </div>

        <!-- Recipes by Status -->
        <div class="card" style="padding: 1.5rem;">
            <?php
            // Set title based on filter
            $pageTitle = '';
            $emptyMessage = '';
            switch ($filterStatus) {
                case 'pending':
                    $pageTitle = 'Resep Menunggu Validasi';
                    $emptyMessage = 'Tidak ada resep yang menunggu validasi.';
                    break;
                case 'approved':
                    $pageTitle = 'Resep yang Disetujui';
                    $emptyMessage = 'Belum ada resep yang disetujui.';
                    break;
                case 'rejected':
                    $pageTitle = 'Resep yang Ditolak';
                    $emptyMessage = 'Belum ada resep yang ditolak.';
                    break;
            }
            ?>
            <h2 style="margin-bottom: 1.5rem;"><?php echo $pageTitle; ?></h2>

            <?php if (count($recipes) === 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo $emptyMessage; ?>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($recipes as $recipe): ?>
                        <?php
                        $ingredients = json_decode($recipe['ingredients'], true) ?? [];
                        $steps = json_decode($recipe['steps'], true) ?? [];
                        ?>
                        <div class="card" style="padding: 1.5rem; border: 2px solid #f59e0b;">
                            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1.5rem;">
                                <!-- Recipe Image -->
                                <div>
                                    <?php if (!empty($recipe['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                                            alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                                            style="width: 100%; height: 150px; object-fit: cover; border-radius: 0.5rem;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 150px; background: var(--color-bg-light); display: flex; align-items: center; justify-content: center; border-radius: 0.5rem;">
                                            <i class="fas fa-image" style="font-size: 3rem; color: var(--color-text-gray);"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Recipe Details -->
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                        <div>
                                            <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                            <p class="text-gray text-sm">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($recipe['author_name']); ?> •
                                                <i class="fas fa-clock"></i> <?php echo date('d M Y', strtotime($recipe['created_at'])); ?>
                                            </p>
                                        </div>
                                        <?php
                                        // Display status badge
                                        $statusBadge = '';
                                        switch ($recipe['status']) {
                                            case 'pending':
                                                $statusBadge = '<span class="badge" style="background: #f59e0b; color: white;"><i class="fas fa-clock"></i> Menunggu Validasi</span>';
                                                break;
                                            case 'approved':
                                                $statusBadge = '<span class="badge" style="background: #10b981; color: white;"><i class="fas fa-check"></i> Disetujui</span>';
                                                break;
                                            case 'rejected':
                                                $statusBadge = '<span class="badge" style="background: #ef4444; color: white;"><i class="fas fa-times"></i> Ditolak</span>';
                                                break;
                                        }
                                        echo $statusBadge;
                                        ?>
                                    </div>

                                    <p class="text-gray" style="margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars(substr($recipe['description'], 0, 150)); ?>...
                                    </p>

                                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-utensils"></i> <?php echo htmlspecialchars($recipe['category']); ?>
                                        </span>
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($recipe['region']); ?>
                                        </span>
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-clock"></i> <?php echo htmlspecialchars($recipe['cooking_time']); ?>
                                        </span>
                                        <?php if (!empty($recipe['servings'])): ?>
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-users"></i> <?php echo htmlspecialchars($recipe['servings']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Rejection reason (for rejected recipes) -->
                                    <?php if ($recipe['status'] === 'rejected' && !empty($recipe['rejection_reason'])): ?>
                                        <div class="alert" style="background: #fef2f2; border-left: 4px solid #ef4444; margin-bottom: 1rem;">
                                            <strong><i class="fas fa-exclamation-circle"></i> Alasan Penolakan:</strong>
                                            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($recipe['rejection_reason']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="../recipe/recipe_detail.php?id=<?php echo $recipe['id']; ?>"
                                            class="btn btn-sm"
                                            style="background: #3b82f6; color: white;"
                                            target="_blank">
                                            <i class="fas fa-eye"></i> Lihat Detail
                                        </a>
                                        
                                        <?php if ($recipe['status'] === 'pending'): ?>
                                            <button onclick="approveRecipe(<?php echo $recipe['id']; ?>)"
                                                class="btn btn-sm"
                                                style="background: #10b981; color: white;">
                                                <i class="fas fa-check"></i> Setujui
                                            </button>
                                            <button onclick="rejectRecipe(<?php echo $recipe['id']; ?>)"
                                                class="btn btn-sm"
                                                style="background: #ef4444; color: white;">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function approveRecipe(recipeId) {
            Swal.fire({
                title: 'Setujui Resep?',
                text: 'Resep ini akan dipublikasikan dan terlihat oleh semua user.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="recipe_id" value="${recipeId}">
                        <input type="hidden" name="action" value="approve">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function rejectRecipe(recipeId) {
            Swal.fire({
                title: 'Tolak Resep?',
                text: 'Berikan alasan penolakan:',
                input: 'textarea',
                inputPlaceholder: 'Contoh: Gambar tidak jelas, deskripsi kurang lengkap, dll.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan harus diisi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="recipe_id" value="${recipeId}">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="rejection_reason" value="${result.value}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        <?php if (isset($message)): ?>
            Swal.fire({
                icon: '<?php echo $type; ?>',
                title: '<?php echo $type === "success" ? "Berhasil!" : "Gagal!"; ?>',
                text: '<?php echo $message; ?>',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'approve_recipes.php';
            });
        <?php endif; ?>
    </script>
    <script src="../../assets/js/dropdown.js"></script>
</body>

</html>