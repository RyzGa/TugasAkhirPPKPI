<?php
// Detail Resep
require_once '../../config/functions.php';
require_once '../../config/database.php';

$user = getCurrentUser();
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipeId === 0) {
    header('Location: ../../index.php');
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT r.*, 
                        (SELECT COUNT(*) FROM liked_recipes WHERE recipe_id = r.id AND user_id = ?) as is_liked
                        FROM recipes r WHERE r.id = ?");
$userId = $user ? $user['id'] : 0;
$stmt->bind_param("ii", $userId, $recipeId);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe) {
    header('Location: ../../index.php');
    exit;
}

// Parse JSON ingredients dan steps untuk ditampilkan
$ingredients = json_decode($recipe['ingredients'], true) ?? [];
$steps = json_decode($recipe['steps'], true) ?? [];

$reviewStmt = $conn->prepare("SELECT * FROM reviews WHERE recipe_id = ? ORDER BY created_at DESC");
$reviewStmt->bind_param("i", $recipeId);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$actualReviewCount = count($reviews);
$actualRating = 0;
if ($actualReviewCount > 0) {
    $totalRating = 0;
    foreach ($reviews as $review) {
        $totalRating += $review['rating'];
    }
    $actualRating = $totalRating / $actualReviewCount;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && $user) {
    $rating = (int)$_POST['rating'];
    $comment = sanitizeInput($_POST['comment']);

    $insertReview = $conn->prepare("INSERT INTO reviews (recipe_id, user_id, author_name, author_avatar, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
    $insertReview->bind_param("iissis", $recipeId, $user['id'], $user['name'], $user['avatar'], $rating, $comment);

    if ($insertReview->execute()) {
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

    <!-- Open Graph Meta Tags for Social Media Sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($recipe['title']); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($recipe['description'], 0, 200)); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($recipe['image']); ?>" />
    <meta property="og:url" content="<?php echo htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="Nusa Bites" />

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo htmlspecialchars($recipe['title']); ?>" />
    <meta name="twitter:description" content="<?php echo htmlspecialchars(substr($recipe['description'], 0, 200)); ?>" />
    <meta name="twitter:image" content="<?php echo htmlspecialchars($recipe['image']); ?>" />

    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php if (isset($_GET['success'])): ?>
        <script>
            <?php if ($_GET['success'] === 'added'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Resep berhasil ditambahkan!',
                    confirmButtonColor: '#28a745',
                    timer: 3000
                });
            <?php elseif ($_GET['success'] === 'updated'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Resep berhasil diupdate!',
                    confirmButtonColor: '#28a745',
                    timer: 3000
                });
            <?php endif; ?>
        </script>
    <?php endif; ?>
    <!-- Header -->
    <header class="header">
        <div class="container header-content">
            <a href="../../index.php" class="logo">
                <img src="../../assets/images/logo.png" alt="NusaBites Logo" style="height: 40px;">
            </a>

            <nav class="nav-links">
                <a href="../../index.php" class="<?php echo isActivePage('index.php'); ?>">Beranda</a>
                <?php if ($user): ?>
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
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-sm btn-secondary">Masuk</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container" style="padding: 2rem 1rem; max-width: 1200px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <a href="../../index.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <div style="display: flex; gap: 0.5rem; position: relative;">
                <!-- Share Button -->
                <button onclick="toggleShareMenu()" class="btn btn-secondary">
                    <i class="fas fa-share-alt"></i> Share
                </button>

                <!-- Share Menu Dropdown -->
                <div id="shareMenu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 0.5rem; background: white; border-radius: 0.5rem; box-shadow: var(--shadow-lg); padding: 0.5rem; z-index: 1000; min-width: 220px;">
                    <button onclick="shareWhatsApp()" class="share-option" style="width: 100%; text-align: left; padding: 0.75rem; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; border-radius: 0.375rem;">
                        <i class="fab fa-whatsapp" style="color: #25D366; font-size: 1.25rem;"></i>
                        <span>WhatsApp</span>
                    </button>
                    <button onclick="shareFacebook()" class="share-option" style="width: 100%; text-align: left; padding: 0.75rem; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; border-radius: 0.375rem;">
                        <i class="fab fa-facebook" style="color: #1877F2; font-size: 1.25rem;"></i>
                        <span>Facebook</span>
                    </button>
                    <button onclick="shareTwitter()" class="share-option" style="width: 100%; text-align: left; padding: 0.75rem; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; border-radius: 0.375rem;">
                        <i class="fab fa-twitter" style="color: #1DA1F2; font-size: 1.25rem;"></i>
                        <span>Twitter</span>
                    </button>
                    <button onclick="copyLink()" class="share-option" style="width: 100%; text-align: left; padding: 0.75rem; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; border-radius: 0.375rem;">
                        <i class="fas fa-link" style="color: #6b7280; font-size: 1.25rem;"></i>
                        <span>Copy Link</span>
                    </button>
                </div>

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
        <?php
        $recipeImage = $recipe['image'];
        if (!empty($recipeImage) && strpos($recipeImage, 'http') !== 0) {
            $recipeImage = '../../' . $recipeImage;
        }
        ?>
        <div style="position: relative; height: 400px; border-radius: 1rem; overflow: hidden; margin-bottom: 2rem; box-shadow: var(--shadow-xl);">
            <?php if (!empty($recipe['image'])): ?>
                <img src="<?php echo htmlspecialchars($recipeImage); ?>"
                    alt="<?php echo htmlspecialchars($recipe['title']); ?>"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    onerror="this.onerror=null; this.src='https://via.placeholder.com/800x600/f59e0b/ffffff?text=Gambar+Tidak+Tersedia'">
            <?php else: ?>
                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-utensils" style="font-size: 5rem; color: white; opacity: 0.5;"></i>
                </div>
            <?php endif; ?>
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

                    <div style="display: flex; gap: 1.5rem; color: var(--color-text-gray); margin-bottom: 1rem; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <?php
                            $authorAvatar = $recipe['author_avatar'];
                            if (!empty($authorAvatar) && strpos($authorAvatar, 'http') !== 0) {
                                $authorAvatar = '../../' . $authorAvatar;
                            }
                            ?>
                            <?php if (!empty($recipe['author_avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($authorAvatar); ?>"
                                    alt="<?php echo htmlspecialchars($recipe['author_name']); ?>"
                                    style="width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 2rem; height: 2rem; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="font-size: 0.875rem; color: white;"></i>
                                </div>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($recipe['author_name']); ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($recipe['cooking_time']); ?></span>
                        </div>
                        <?php if (!empty($recipe['servings'])): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-utensils"></i>
                                <span><?php echo htmlspecialchars($recipe['servings']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-star star-icon"></i>
                            <span><?php echo $actualReviewCount > 0 ? number_format($actualRating, 1) : '0.0'; ?> (<?php echo $actualReviewCount; ?> review)</span>
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
                            <a href="../auth/login.php" style="color: inherit; text-decoration: underline;">Login</a> untuk memberikan review
                        </div>
                    <?php endif; ?>

                    <!-- Review List -->
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if (count($reviews) > 0): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="card">
                                    <div class="card-content">
                                        <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
                                            <?php
                                            $reviewAvatar = $review['author_avatar'];
                                            if (!empty($reviewAvatar) && strpos($reviewAvatar, 'http') !== 0) {
                                                $reviewAvatar = '../../' . $reviewAvatar;
                                            }
                                            ?>
                                            <?php if (!empty($review['author_avatar'])): ?>
                                                <img src="<?php echo htmlspecialchars($reviewAvatar); ?>"
                                                    alt="<?php echo htmlspecialchars($review['author_name']); ?>"
                                                    style="width: 2.5rem; height: 2.5rem; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-user" style="font-size: 1rem; color: white;"></i>
                                                </div>
                                            <?php endif; ?>
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
                                    <span style="font-size: 1.5rem; font-weight: 700;"><?php echo $actualReviewCount > 0 ? number_format($actualRating, 1) : '0.0'; ?></span>
                                    <span class="text-gray">(<?php echo $actualReviewCount; ?> review)</span>
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
            Swal.fire({
                title: 'Hapus Resep?',
                text: 'Resep yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../../api/delete_recipe.php?id=' + recipeId;
                }
            });
        }

        // Share Functions
        const recipeUrl = window.location.href.split('&success=')[0]; // Remove success param
        const recipeTitle = '<?php echo addslashes($recipe["title"]); ?>';
        const recipeDescription = '<?php echo addslashes(substr($recipe["description"], 0, 150)); ?>';
        const recipeImage = '<?php echo addslashes($recipe["image"]); ?>';
        const recipeAuthor = '<?php echo addslashes($recipe["author_name"]); ?>';
        const recipeRating = '<?php echo number_format($actualRating, 1); ?>';
        const recipeCookingTime = '<?php echo addslashes($recipe["cooking_time"]); ?>';

        function toggleShareMenu() {
            const menu = document.getElementById('shareMenu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        // Close share menu when clicking outside
        document.addEventListener('click', function(event) {
            const shareMenu = document.getElementById('shareMenu');
            const shareButton = event.target.closest('button[onclick="toggleShareMenu()"]');

            if (!shareButton && !shareMenu.contains(event.target)) {
                shareMenu.style.display = 'none';
            }
        });

        function shareWhatsApp() {
            const text = `🍴 *${recipeTitle}*\n\n` +
                `📝 ${recipeDescription}\n\n` +
                `👨‍🍳 Oleh: ${recipeAuthor}\n` +
                `⭐ Rating: ${recipeRating}/5.0\n` +
                `⏱️ Waktu Memasak: ${recipeCookingTime}\n\n` +
                `📸 Gambar: ${recipeImage}\n\n` +
                `🔗 Lihat resep lengkapnya di:\n${recipeUrl}`;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
            window.open(whatsappUrl, '_blank');
            toggleShareMenu();
        }

        function shareFacebook() {
            // Facebook akan otomatis baca Open Graph meta tags untuk gambar dan deskripsi
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(recipeUrl)}&quote=${encodeURIComponent(recipeTitle + ' - ' + recipeDescription)}`;
            window.open(facebookUrl, '_blank', 'width=600,height=400');
            toggleShareMenu();
        }

        function shareTwitter() {
            const text = `🍴 ${recipeTitle}\n\n${recipeDescription}\n\n⭐ ${recipeRating}/5.0 | ⏱️ ${recipeCookingTime}\n👨‍🍳 By ${recipeAuthor}`;
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(recipeUrl)}`;
            window.open(twitterUrl, '_blank', 'width=600,height=400');
            toggleShareMenu();
        }

        function copyLink() {
            navigator.clipboard.writeText(recipeUrl).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Link berhasil disalin ke clipboard',
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    showConfirmButton: false
                });
                toggleShareMenu();
            }).catch(err => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = recipeUrl;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Link berhasil disalin',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyalin link',
                        confirmButtonColor: '#d33'
                    });
                }
                document.body.removeChild(textArea);
                toggleShareMenu();
            });
        }
    </script>
    <script src="../../assets/js/dropdown.js"></script>
    <?php include '../../includes/footer.php'; ?>
</body>

</html>