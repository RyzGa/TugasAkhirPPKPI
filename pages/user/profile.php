<?php
require_once '../../config/functions.php';
require_once '../../config/database.php';

requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

// Get user's recipes with actual review counts and ratings
$myRecipesStmt = $conn->prepare("SELECT r.*, 
                                  (SELECT COUNT(*) FROM reviews WHERE recipe_id = r.id) as actual_review_count,
                                  (SELECT AVG(rating) FROM reviews WHERE recipe_id = r.id) as actual_rating
                                  FROM recipes r WHERE r.author_id = ? ORDER BY r.created_at DESC");
$myRecipesStmt->bind_param("i", $user['id']);
$myRecipesStmt->execute();
$myRecipes = $myRecipesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get liked recipes with actual review counts and ratings
$likedStmt = $conn->prepare("SELECT r.*, 
                             (SELECT COUNT(*) FROM reviews WHERE recipe_id = r.id) as actual_review_count,
                             (SELECT AVG(rating) FROM reviews WHERE recipe_id = r.id) as actual_rating
                             FROM recipes r 
                             INNER JOIN liked_recipes l ON r.id = l.recipe_id 
                             WHERE l.user_id = ? 
                             ORDER BY l.created_at DESC");
$likedStmt->bind_param("i", $user['id']);
$likedStmt->execute();
$likedRecipes = $likedStmt->get_result()->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Nusa Bites</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <div class="container" style="padding: 2rem 1rem;">
        <!-- Profile Header -->
        <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <?php
                // Handle avatar path - add ../../ prefix for local uploads
                $avatarSrc = $user['avatar'];
                if (!empty($avatarSrc) && strpos($avatarSrc, 'http') !== 0) {
                    // Local file, add relative path
                    $avatarSrc = '../../' . $avatarSrc;
                }
                ?>
                <?php if (!empty($avatarSrc)): ?>
                    <img src="<?php echo htmlspecialchars($avatarSrc); ?>"
                        alt="<?php echo htmlspecialchars($user['name']); ?>"
                        style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--color-primary); object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--color-primary); background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user" style="font-size: 3rem; color: white;"></i>
                    </div>
                <?php endif; ?>
                <div style="flex: 1;">
                    <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p class="text-gray" style="margin-bottom: 0.5rem;">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <span class="badge badge-primary">
                        <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'crown' : 'user'; ?>"></i>
                        <?php echo $user['role'] === 'admin' ? 'Admin' : 'User'; ?>
                    </span>
                    <div style="margin-top: 1rem;">
                        <a href="edit_profile.php" class="btn btn-secondary">
                            <i class="fas fa-edit"></i> Edit Profil
                        </a>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 1rem; text-align: center;">
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--color-primary);"><?php echo count($myRecipes); ?></div>
                        <div class="text-gray text-sm">Resep</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--color-primary);"><?php echo count($likedRecipes); ?></div>
                        <div class="text-gray text-sm">Favorit</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div style="margin-bottom: 2rem; border-bottom: 2px solid var(--color-border);">
            <div style="display: flex; gap: 2rem;">
                <button onclick="showTab('my-recipes')" id="tab-my-recipes" class="tab-button active" style="padding: 1rem; background: none; border: none; border-bottom: 3px solid var(--color-primary); cursor: pointer; font-weight: 600; color: var(--color-primary);">
                    <i class="fas fa-book"></i> Resep Saya (<?php echo count($myRecipes); ?>)
                </button>
                <button onclick="showTab('liked')" id="tab-liked" class="tab-button" style="padding: 1rem; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; color: var(--color-text-gray);">
                    <i class="fas fa-heart"></i> Favorit (<?php echo count($likedRecipes); ?>)
                </button>
            </div>
        </div>

        <!-- My Recipes Tab -->
        <div id="content-my-recipes" class="tab-content">
            <?php if (count($myRecipes) > 0): ?>
                <div class="grid recipe-grid">
                    <?php foreach ($myRecipes as $recipe): ?>
                        <div class="card recipe-card" onclick="window.location.href='../recipe/recipe_detail.php?id=<?php echo $recipe['id']; ?>'">
                            <div class="recipe-card-image-wrapper">
                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                                    alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                                    class="recipe-card-image"
                                    onerror="this.src='../../assets/images/placeholder.jpg'">
                                <span class="recipe-card-badge"><?php echo htmlspecialchars($recipe['category']); ?></span>
                            </div>
                            <div class="card-content">
                                <h3 class="recipe-card-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <div class="recipe-card-footer">
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-clock icon-sm"></i>
                                        <span><?php echo htmlspecialchars($recipe['cooking_time']); ?></span>
                                    </div>
                                    <div class="recipe-card-rating">
                                        <i class="fas fa-star star-icon"></i>
                                        <span><?php echo $recipe['actual_review_count'] > 0 ? number_format($recipe['actual_rating'], 1) : '0.0'; ?></span>
                                        <span class="text-gray">(<?php echo $recipe['actual_review_count']; ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card" style="padding: 3rem; text-align: center;">
                    <i class="fas fa-book" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Belum ada resep</h3>
                    <p class="text-gray" style="margin-bottom: 1.5rem;">Mulai berbagi resep favorit Anda dengan komunitas!</p>
                    <a href="../recipe/add_recipe.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Resep
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Liked Recipes Tab -->
        <div id="content-liked" class="tab-content" style="display: none;">
            <?php if (count($likedRecipes) > 0): ?>
                <div class="grid recipe-grid">
                    <?php foreach ($likedRecipes as $recipe): ?>
                        <div class="card recipe-card" onclick="window.location.href='../recipe/recipe_detail.php?id=<?php echo $recipe['id']; ?>'">
                            <div class="recipe-card-image-wrapper">
                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                                    alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                                    class="recipe-card-image"
                                    onerror="this.src='../../assets/images/placeholder.jpg'">
                                <span class="recipe-card-badge"><?php echo htmlspecialchars($recipe['category']); ?></span>
                            </div>
                            <div class="card-content">
                                <h3 class="recipe-card-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <div class="recipe-card-meta">
                                    <i class="fas fa-user icon-sm"></i>
                                    <span><?php echo htmlspecialchars($recipe['author_name']); ?></span>
                                </div>
                                <div class="recipe-card-footer">
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-clock icon-sm"></i>
                                        <span><?php echo htmlspecialchars($recipe['cooking_time']); ?></span>
                                    </div>
                                    <div class="recipe-card-rating">
                                        <i class="fas fa-star star-icon"></i>
                                        <span><?php echo $recipe['actual_review_count'] > 0 ? number_format($recipe['actual_rating'], 1) : '0.0'; ?></span>
                                        <span class="text-gray">(<?php echo $recipe['actual_review_count']; ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card" style="padding: 3rem; text-align: center;">
                    <i class="fas fa-heart" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3>Belum ada favorit</h3>
                    <p class="text-gray" style="margin-bottom: 1.5rem;">Tandai resep favorit Anda untuk akses cepat!</p>
                    <a href="../../index.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Jelajahi Resep
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.style.borderBottomColor = 'transparent';
                button.style.color = 'var(--color-text-gray)';
            });

            // Show selected tab
            document.getElementById('content-' + tabName).style.display = 'block';

            // Add active class to selected button
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.style.borderBottomColor = 'var(--color-primary)';
            activeButton.style.color = 'var(--color-primary)';
        }
    </script>
    <script src="../../assets/js/dropdown.js"></script>
    <?php include '../../includes/footer.php'; ?>
</body>

</html>