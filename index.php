<?php
require_once 'config/functions.php';
require_once 'config/database.php';

$conn = getDBConnection();
$user = getCurrentUser();

// Get filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$categories = isset($_GET['categories']) ? $_GET['categories'] : [];
$regions = isset($_GET['regions']) ? $_GET['regions'] : [];
$cookingTime = isset($_GET['cooking_time']) ? sanitizeInput($_GET['cooking_time']) : 'all';
$sortRating = isset($_GET['sort_rating']) ? sanitizeInput($_GET['sort_rating']) : 'newest';

// Build query
$query = "SELECT r.*, 
          (SELECT COUNT(*) FROM liked_recipes WHERE recipe_id = r.id AND user_id = ?) as is_liked
          FROM recipes r WHERE 1=1";
$params = [$user ? $user['id'] : 0];
$types = "i";

if ($search) {
    $query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (!empty($categories)) {
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $query .= " AND r.category IN ($placeholders)";
    foreach ($categories as $cat) {
        $params[] = $cat;
        $types .= "s";
    }
}

if (!empty($regions)) {
    $placeholders = implode(',', array_fill(0, count($regions), '?'));
    $query .= " AND r.region IN ($placeholders)";
    foreach ($regions as $reg) {
        $params[] = $reg;
        $types .= "s";
    }
}

// Sorting
switch ($sortRating) {
    case 'highest':
        $query .= " ORDER BY r.rating DESC, r.created_at DESC";
        break;
    case 'lowest':
        $query .= " ORDER BY r.rating ASC, r.created_at DESC";
        break;
    default: // newest
        $query .= " ORDER BY r.created_at DESC";
        break;
}

$stmt = $conn->prepare($query);
if (count($params) > 1) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, $params[0]);
}
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);

// Get liked recipes for current user
$likedRecipes = [];
if ($user) {
    $likedQuery = "SELECT recipe_id FROM liked_recipes WHERE user_id = ?";
    $likedStmt = $conn->prepare($likedQuery);
    $likedStmt->bind_param("i", $user['id']);
    $likedStmt->execute();
    $likedResult = $likedStmt->get_result();
    while ($row = $likedResult->fetch_assoc()) {
        $likedRecipes[] = $row['recipe_id'];
    }
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nusa Bites - Resep Masakan Nusantara</title>
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

            <div class="search-bar">
                <form method="GET" action="index.php">
                    <input type="text" name="search" class="search-input" placeholder="Cari resep..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
            </div>

            <nav class="nav-links">
                <a href="index.php" class="<?php echo isActivePage('index.php'); ?>">Beranda</a>
                <a href="about.php" class="<?php echo isActivePage('about.php'); ?>">Tentang</a>
                <a href="contact.php" class="<?php echo isActivePage('contact.php'); ?>">Kontak</a>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="pages/admin/admin.php" class="<?php echo isActivePage('admin.php'); ?>">Admin</a>
                    <?php endif; ?>
                    <a href="pages/recipe/add_recipe.php" class="<?php echo isActivePage('add_recipe.php'); ?>">
                        <i class="fas fa-plus"></i> Tambah Resep
                    </a>
                    <a href="pages/user/profile.php" class="user-profile-link <?php echo isActivePage('profile.php'); ?>">
                        <img src="<?php echo htmlspecialchars($user['avatar'] ?: 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($user['name'])); ?>" 
                             alt="<?php echo htmlspecialchars($user['name']); ?>" 
                             class="avatar">
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </a>
                    <a href="pages/auth/logout.php">Keluar</a>
                <?php else: ?>
                    <a href="pages/auth/login.php" class="btn btn-sm btn-secondary">Masuk</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container" style="padding: 2rem 1rem;">
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem;">
            <!-- Sidebar Filter -->
            <aside class="filter-sidebar">
                <h3 class="filter-title">Filter Resep</h3>

                <form method="GET" action="index.php" id="filterForm">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

                    <!-- Category Filter -->
                    <div class="filter-section">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">Kategori</h4>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" name="categories[]" value="Makanan Utama"
                                    <?php echo in_array('Makanan Utama', $categories) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span>Makanan Utama</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="categories[]" value="Camilan"
                                    <?php echo in_array('Camilan', $categories) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span>Camilan</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="categories[]" value="Minuman"
                                    <?php echo in_array('Minuman', $categories) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span>Minuman</span>
                            </label>
                        </div>
                    </div>

                    <!-- Region Filter -->
                    <div class="filter-section">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">Region</h4>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" name="regions[]" value="Jawa"
                                    <?php echo in_array('Jawa', $regions) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span>Jawa</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="regions[]" value="Sumatera"
                                    <?php echo in_array('Sumatera', $regions) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span>Sumatera</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="regions[]" value="Kalimantan"
                                    <?php echo in_array('Kalimantan', $regions) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span>Kalimantan</span>
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="regions[]" value="Sulawesi"
                                    <?php echo in_array('Sulawesi', $regions) ? 'checked' : ''; ?>
                                    onchange="this.form.submit()">
                                <span>Sulawesi</span>
                            </label>
                        </div>
                    </div>

                    <!-- Urutan Rating -->
                    <div class="filter-section">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem;">Urutan Rating</h4>
                        <select name="sort_rating" class="form-select" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sortRating == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="highest" <?php echo $sortRating == 'highest' ? 'selected' : ''; ?>>Rating Tertinggi</option>
                            <option value="lowest" <?php echo $sortRating == 'lowest' ? 'selected' : ''; ?>>Rating Terendah</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;" onclick="window.location.href='index.php'">
                        Reset Filter
                    </button>
                </form>
            </aside>

            <!-- Recipe List -->
            <main>
                <div style="margin-bottom: 1.5rem;">
                    <h2>Resep Masakan Nusantara</h2>
                    <p class="text-gray">Temukan <?php echo count($recipes); ?> resep lezat dari berbagai daerah di Indonesia</p>
                </div>

                <?php if (count($recipes) > 0): ?>
                    <div class="grid recipe-grid">
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="card recipe-card" onclick="window.location.href='pages/recipe/recipe_detail.php?id=<?php echo $recipe['id']; ?>'">
                                <div class="recipe-card-image-wrapper">
                                    <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                                        alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                                        class="recipe-card-image"
                                        onerror="this.src='assets/images/placeholder.jpg'">
                                    <span class="recipe-card-badge"><?php echo htmlspecialchars($recipe['category']); ?></span>

                                    <?php if ($user): ?>
                                        <div class="recipe-card-actions">
                                            <button class="btn btn-sm" style="background: rgba(255,255,255,0.9); width: 2rem; height: 2rem; padding: 0;"
                                                onclick="event.stopPropagation(); toggleLike(<?php echo $recipe['id']; ?>)">
                                                <i class="fa<?php echo in_array($recipe['id'], $likedRecipes) ? 's' : 'r'; ?> fa-heart"
                                                    style="color: <?php echo in_array($recipe['id'], $likedRecipes) ? '#ef4444' : '#6b7280'; ?>"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
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
                                            <span class="text-gray">(<?php echo $recipe['review_count']; ?>)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card" style="padding: 3rem; text-align: center;">
                        <i class="fas fa-hat-chef" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                        <h3>Tidak ada resep ditemukan</h3>
                        <p class="text-gray">Coba ubah filter atau kata kunci pencarian Anda.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        function toggleLike(recipeId) {
            fetch('api/toggle_like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        recipe_id: recipeId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>