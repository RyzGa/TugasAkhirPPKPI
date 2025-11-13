<?php
require_once 'config/functions.php';
require_once 'config/database.php';

requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

// Get user's recipes
$myRecipesStmt = $conn->prepare("SELECT * FROM recipes WHERE author_id = ? ORDER BY created_at DESC");
$myRecipesStmt->bind_param("i", $user['id']);
$myRecipesStmt->execute();
$myRecipes = $myRecipesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get liked recipes
$likedStmt = $conn->prepare("SELECT r.* FROM recipes r 
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

    <div class="container" style="padding: 2rem 1rem;">
        <!-- Profile Header -->
        <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>"
                    alt="<?php echo htmlspecialchars($user['name']); ?>"
                    style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--color-primary);"
                    onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=User'">
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($myRecipes as $recipe): ?>
                        <div class="card recipe-card" onclick="window.location.href='recipe_detail.php?id=<?php echo $recipe['id']; ?>'">
                            <div class="recipe-card-image-wrapper">
                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                                    alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                                    class="recipe-card-image"
                                    onerror="this.src='assets/images/placeholder.jpg'">
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
                                        <span><?php echo number_format($recipe['rating'], 1); ?></span>
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
                    <a href="add_recipe.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Resep
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Liked Recipes Tab -->
        <div id="content-liked" class="tab-content" style="display: none;">
            <?php if (count($likedRecipes) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($likedRecipes as $recipe): ?>
                        <div class="card recipe-card" onclick="window.location.href='recipe_detail.php?id=<?php echo $recipe['id']; ?>'">
                            <div class="recipe-card-image-wrapper">
                                <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                                    alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                                    class="recipe-card-image"
                                    onerror="this.src='assets/images/placeholder.jpg'">
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
                                        <span><?php echo number_format($recipe['rating'], 1); ?></span>
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
                    <a href="index.php" class="btn btn-primary">
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
</body>

</html>