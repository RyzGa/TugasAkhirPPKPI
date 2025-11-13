<?php
require_once 'config/functions.php';
require_once 'config/database.php';

$user = getCurrentUser();
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($recipeId === 0) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Get recipe detail
$stmt = $conn->prepare("SELECT r.*, 
                        (SELECT COUNT(*) FROM liked_recipes WHERE recipe_id = r.id AND user_id = ?) as is_liked
                        FROM recipes r WHERE r.id = ?");
$userId = $user ? $user['id'] : 0;
$stmt->bind_param("ii", $userId, $recipeId);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe) {
    header('Location: index.php');
    exit;
}

// Parse ingredients and steps from JSON
$ingredients = json_decode($recipe['ingredients'], true);
$steps = json_decode($recipe['steps'], true);

// Get reviews
$reviewStmt = $conn->prepare("SELECT * FROM reviews WHERE recipe_id = ? ORDER BY created_at DESC");
$reviewStmt->bind_param("i", $recipeId);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && $user) {
    $rating = (int)$_POST['rating'];
    $comment = sanitizeInput($_POST['comment']);

    $insertReview = $conn->prepare("INSERT INTO reviews (recipe_id, user_id, author_name, author_avatar, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
    $insertReview->bind_param("iissis", $recipeId, $user['id'], $user['name'], $user['avatar'], $rating, $comment);

    if ($insertReview->execute()) {
        // Update recipe rating
        $updateRating = $conn->prepare("UPDATE recipes SET 
                                        rating = (SELECT AVG(rating) FROM reviews WHERE recipe_id = ?),
                                        review_count = (SELECT COUNT(*) FROM reviews WHERE recipe_id = ?)
                                        WHERE id = ?");
        $updateRating->bind_param("iii", $recipeId, $recipeId, $recipeId);
        $updateRating->execute();

        header("Location: recipe_detail.php?id=$recipeId");
        exit;
    }
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - Nusa Bites</title>
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
                <?php if ($user): ?>
                    <a href="profile.php">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['name']); ?>
                    </a>
                    <a href="logout.php">Keluar</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-secondary">Masuk</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container" style="padding: 2rem 1rem; max-width: 1200px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <a href="index.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <div style="display: flex; gap: 0.5rem;">
                <?php if ($user && $user['role'] !== 'admin'): ?>
                    <button onclick="toggleLike(<?php echo $recipe['id']; ?>)"
                        class="btn <?php echo $recipe['is_liked'] ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fa<?php echo $recipe['is_liked'] ? 's' : 'r'; ?> fa-heart"></i>
                        <?php echo $recipe['is_liked'] ? 'Hapus dari Favorit' : 'Simpan ke Favorit'; ?>
                    </button>
                <?php endif; ?>

                <?php if ($user && ($user['role'] === 'admin' || $user['id'] == $recipe['author_id'])): ?>
                    <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn" style="background: #3b82f6; color: white;">
                        <i class="fas fa-edit"></i> Edit Resep
                    </a>
                    <button onclick="confirmDelete(<?php echo $recipe['id']; ?>)" class="btn" style="background: #ef4444; color: white;">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hero Image -->
        <div style="position: relative; height: 400px; border-radius: 1rem; overflow: hidden; margin-bottom: 2rem; box-shadow: var(--shadow-xl);">
            <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                style="width: 100%; height: 100%; object-fit: cover;"
                onerror="this.src='assets/images/placeholder.jpg'">
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Main Content -->
            <div>
                <!-- Title and Meta -->
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <span class="badge badge-primary"><?php echo htmlspecialchars($recipe['category']); ?></span>
                        <span class="badge badge-secondary"><?php echo htmlspecialchars($recipe['region']); ?></span>
                    </div>

                    <h1 style="margin-bottom: 1rem;"><?php echo htmlspecialchars($recipe['title']); ?></h1>

                    <div style="display: flex; gap: 1.5rem; color: var(--color-text-gray); margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($recipe['author_name']); ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($recipe['cooking_time']); ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-star star-icon"></i>
                            <span><?php echo number_format($recipe['rating'], 1); ?> (<?php echo $recipe['review_count']; ?> review)</span>
                        </div>
                    </div>

                    <p class="text-gray"><?php echo htmlspecialchars($recipe['description']); ?></p>
                </div>

                <hr style="border: none; border-top: 1px solid var(--color-border); margin: 2rem 0;">

                <!-- Ingredients -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem;">Bahan-Bahan</h2>
                    <div class="card">
                        <div class="card-content">
                            <ul style="list-style: none; padding: 0;">
                                <?php foreach ($ingredients as $ingredient): ?>
                                    <li style="display: flex; align-items: start; gap: 0.75rem; margin-bottom: 0.75rem;">
                                        <div style="width: 8px; height: 8px; background: var(--color-primary); border-radius: 50%; margin-top: 0.5rem; flex-shrink: 0;"></div>
                                        <span><?php echo htmlspecialchars($ingredient); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Steps -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1rem;">Langkah-Langkah</h2>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($steps as $index => $step): ?>
                            <div class="card">
                                <div class="card-content">
                                    <div style="display: flex; gap: 1rem;">
                                        <div style="flex-shrink: 0; width: 2rem; height: 2rem; background: var(--color-primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                            <?php echo $index + 1; ?>
                                        </div>
                                        <p style="flex: 1; margin: 0;"><?php echo htmlspecialchars($step); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--color-border); margin: 2rem 0;">

                <!-- Reviews -->
                <div>
                    <h2 style="margin-bottom: 1.5rem;">Review & Rating</h2>

                    <!-- Add Review Form -->
                    <?php if ($user): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-content">
                                <h3 style="margin-bottom: 1rem;">Tulis Review</h3>

                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label class="form-label">Rating</label>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <label style="cursor: pointer;">
                                                    <input type="radio" name="rating" value="<?php echo $i; ?>"
                                                        <?php echo $i === 5 ? 'checked' : ''; ?>
                                                        style="display: none;"
                                                        onchange="updateStars(this)">
                                                    <i class="fas fa-star" style="font-size: 1.5rem; color: #d1d5db;" data-star="<?php echo $i; ?>"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="comment" class="form-label">Komentar</label>
                                        <textarea id="comment" name="comment" class="form-textarea" required
                                            placeholder="Bagikan pengalaman Anda dengan resep ini..."></textarea>
                                    </div>

                                    <button type="submit" name="submit_review" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Kirim Review
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" style="margin-bottom: 2rem;">
                            <i class="fas fa-info-circle"></i>
                            <a href="login.php" style="color: inherit; text-decoration: underline;">Login</a> untuk memberikan review
                        </div>
                    <?php endif; ?>

                    <!-- Review List -->
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if (count($reviews) > 0): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="card">
                                    <div class="card-content">
                                        <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
                                            <img src="<?php echo htmlspecialchars($review['author_avatar']); ?>"
                                                alt="<?php echo htmlspecialchars($review['author_name']); ?>"
                                                style="width: 2.5rem; height: 2.5rem; border-radius: 50%;"
                                                onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=User'">
                                            <div style="flex: 1;">
                                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                                    <div>
                                                        <h4 style="font-size: 1rem; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($review['author_name']); ?></h4>
                                                        <div style="display: flex; gap: 0.25rem; margin-bottom: 0.5rem;">
                                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                                <i class="fas fa-star" style="font-size: 0.875rem; color: <?php echo $i < $review['rating'] ? '#fbbf24' : '#d1d5db'; ?>;"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm text-gray"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                                                </div>
                                                <p style="margin: 0; color: var(--color-text-gray);"><?php echo htmlspecialchars($review['comment']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray text-center">Belum ada review. Jadilah yang pertama!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="card" style="position: sticky; top: 5rem;">
                    <div class="card-content">
                        <h3 style="margin-bottom: 1rem;">Informasi Resep</h3>

                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <div style="color: var(--color-text-gray); font-size: 0.875rem; margin-bottom: 0.25rem;">Waktu Memasak</div>
                                <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-clock" style="color: var(--color-primary);"></i>
                                    <?php echo htmlspecialchars($recipe['cooking_time']); ?>
                                </div>
                            </div>

                            <div>
                                <div style="color: var(--color-text-gray); font-size: 0.875rem; margin-bottom: 0.25rem;">Kategori</div>
                                <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-utensils" style="color: var(--color-primary);"></i>
                                    <?php echo htmlspecialchars($recipe['category']); ?>
                                </div>
                            </div>

                            <div>
                                <div style="color: var(--color-text-gray); font-size: 0.875rem; margin-bottom: 0.25rem;">Region</div>
                                <div style="font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-map-marker-alt" style="color: var(--color-primary);"></i>
                                    <?php echo htmlspecialchars($recipe['region']); ?>
                                </div>
                            </div>

                            <hr style="border: none; border-top: 1px solid var(--color-border); margin: 0.5rem 0;">

                            <div>
                                <div style="color: var(--color-text-gray); font-size: 0.875rem; margin-bottom: 0.25rem;">Rating</div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-star" style="color: #fbbf24; font-size: 1.25rem;"></i>
                                    <span style="font-size: 1.5rem; font-weight: 700;"><?php echo number_format($recipe['rating'], 1); ?></span>
                                    <span class="text-gray">(<?php echo $recipe['review_count']; ?> review)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStars(radio) {
            const value = parseInt(radio.value);
            const stars = document.querySelectorAll('[data-star]');
            stars.forEach(star => {
                const starValue = parseInt(star.getAttribute('data-star'));
                if (starValue <= value) {
                    star.style.color = '#fbbf24';
                } else {
                    star.style.color = '#d1d5db';
                }
            });
        }

        // Initialize stars
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="rating"]:checked');
            if (checkedRadio) {
                updateStars(checkedRadio);
            }
        });

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
                });
        }

        function confirmDelete(recipeId) {
            if (confirm('Apakah Anda yakin ingin menghapus resep ini?')) {
                window.location.href = 'api/delete_recipe.php?id=' + recipeId;
            }
        }
    </script>
</body>

</html>