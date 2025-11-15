<?php
require_once '../../config/functions.php';
require_once '../../config/database.php';

requireLogin();
requireAdmin();

$user = getCurrentUser();
$conn = getDBConnection();

// Get all recipes
$stmt = $conn->prepare("SELECT * FROM recipes ORDER BY created_at DESC");
$stmt->execute();
$recipes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get statistics
$statsQuery = "SELECT 
                (SELECT COUNT(*) FROM recipes) as total_recipes,
                (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                (SELECT COUNT(*) FROM reviews) as total_reviews,
                (SELECT AVG(rating) FROM recipes) as avg_rating";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Nusa Bites</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="../../index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-hat-chef" style="font-size: 1.5rem;"></i>
                </div>
                <span>Nusa Bites</span>
            </a>
            <nav class="nav-links">
                <a href="../../index.php" class="<?php echo isActivePage('index.php'); ?>">Beranda</a>
                <a href="admin.php" class="<?php echo isActivePage('admin.php'); ?>"><i class="fas fa-shield-alt"></i> Admin</a>
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
            <h1><i class="fas fa-shield-alt"></i> Admin Dashboard</h1>
            <p class="text-gray">Kelola semua resep dan pengguna</p>
        </div>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-book" style="font-size: 2rem; color: var(--color-primary); margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $stats['total_recipes']; ?></div>
                <div class="text-gray text-sm">Total Resep</div>
            </div>

            <div class="card" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-users" style="font-size: 2rem; color: #3b82f6; margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $stats['total_users']; ?></div>
                <div class="text-gray text-sm">Total User</div>
            </div>

            <div class="card" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-comments" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo $stats['total_reviews']; ?></div>
                <div class="text-gray text-sm">Total Review</div>
            </div>

            <div class="card" style="padding: 1.5rem; text-align: center;">
                <i class="fas fa-star" style="font-size: 2rem; color: #fbbf24; margin-bottom: 0.5rem;"></i>
                <div style="font-size: 2rem; font-weight: 700; color: var(--color-text-dark);"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                <div class="text-gray text-sm">Rata-rata Rating</div>
            </div>
        </div>

        <!-- Recipes Table -->
        <div class="card" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;">Semua Resep</h2>
                <a href="../recipe/add_recipe.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Resep
                </a>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--color-bg-light); border-bottom: 2px solid var(--color-border);">
                            <th style="padding: 0.75rem; text-align: left;">ID</th>
                            <th style="padding: 0.75rem; text-align: left;">Judul</th>
                            <th style="padding: 0.75rem; text-align: left;">Penulis</th>
                            <th style="padding: 0.75rem; text-align: left;">Kategori</th>
                            <th style="padding: 0.75rem; text-align: center;">Rating</th>
                            <th style="padding: 0.75rem; text-align: center;">Reviews</th>
                            <th style="padding: 0.75rem; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recipes as $recipe): ?>
                            <tr style="border-bottom: 1px solid var(--color-border);">
                                <td style="padding: 0.75rem;"><?php echo $recipe['id']; ?></td>
                                <td style="padding: 0.75rem;">
                                    <a href="../recipe/recipe_detail.php?id=<?php echo $recipe['id']; ?>" style="color: var(--color-primary); text-decoration: none; font-weight: 500;">
                                        <?php echo htmlspecialchars($recipe['title']); ?>
                                    </a>
                                </td>
                                <td style="padding: 0.75rem;"><?php echo htmlspecialchars($recipe['author_name']); ?></td>
                                <td style="padding: 0.75rem;">
                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($recipe['category']); ?></span>
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <i class="fas fa-star" style="color: #fbbf24;"></i>
                                    <?php echo number_format($recipe['rating'], 1); ?>
                                </td>
                                <td style="padding: 0.75rem; text-align: center;"><?php echo $recipe['review_count']; ?></td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="../recipe/recipe_detail.php?id=<?php echo $recipe['id']; ?>"
                                            class="btn btn-sm"
                                            style="background: #3b82f6; color: white; padding: 0.25rem 0.75rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="../recipe/edit_recipe.php?id=<?php echo $recipe['id']; ?>"
                                            class="btn btn-sm"
                                            style="background: #10b981; color: white; padding: 0.25rem 0.75rem;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $recipe['id']; ?>)"
                                            class="btn btn-sm"
                                            style="background: #ef4444; color: white; padding: 0.25rem 0.75rem; border: none; cursor: pointer;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(recipeId) {
            if (confirm('Apakah Anda yakin ingin menghapus resep ini?')) {
                window.location.href = 'api/delete_recipe.php?id=' + recipeId;
            }
        }
    </script>
    <script src="../../assets/js/dropdown.js"></script>
    <?php include '../../includes/footer.php'; ?>
</body>

</html>