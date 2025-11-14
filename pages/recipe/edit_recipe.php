<?php
require_once 'config/functions.php';
require_once 'config/database.php';

requireLogin();

$user = getCurrentUser();
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($recipeId === 0) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

// Get recipe
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipeId);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe) {
    header('Location: index.php');
    exit;
}

// Check permission
if ($user['role'] !== 'admin' && $user['id'] != $recipe['author_id']) {
    header('Location: index.php');
    exit;
}

// Parse ingredients and steps
$ingredients = json_decode($recipe['ingredients'], true);
$steps = json_decode($recipe['steps'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $image = sanitizeInput($_POST['image']);
    $cookingTime = sanitizeInput($_POST['cooking_time']);
    $category = sanitizeInput($_POST['category']);
    $region = sanitizeInput($_POST['region']);

    $newIngredients = array_filter(array_map('trim', explode("\n", $_POST['ingredients'])));
    $newSteps = array_filter(array_map('trim', explode("\n", $_POST['steps'])));

    if (empty($title) || empty($description) || count($newIngredients) === 0 || count($newSteps) === 0) {
        $error = 'Mohon lengkapi semua field yang diperlukan!';
    } else {
        $ingredientsJson = json_encode($newIngredients);
        $stepsJson = json_encode($newSteps);

        $updateStmt = $conn->prepare("UPDATE recipes SET title = ?, description = ?, image = ?, cooking_time = ?, category = ?, region = ?, ingredients = ?, steps = ? WHERE id = ?");
        $updateStmt->bind_param("ssssssssi", $title, $description, $image, $cookingTime, $category, $region, $ingredientsJson, $stepsJson, $recipeId);

        if ($updateStmt->execute()) {
            $success = 'Resep berhasil diupdate!';
            header("Location: recipe_detail.php?id=$recipeId");
            exit;
        } else {
            $error = 'Terjadi kesalahan saat mengupdate resep.';
        }
    }
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resep - Nusa Bites</title>
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
                <a href="index.php" class="<?php echo isActivePage('index.php'); ?>">Beranda</a>
                <a href="profile.php" class="user-profile-link <?php echo isActivePage('profile.php'); ?>">
                    <img src="<?php echo htmlspecialchars($user['avatar'] ?: 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($user['name'])); ?>" 
                         alt="<?php echo htmlspecialchars($user['name']); ?>" 
                         class="avatar">
                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                </a>
                <a href="logout.php">Keluar</a>
            </nav>
        </div>
    </header>

    <div class="container" style="padding: 2rem 1rem; max-width: 900px;">
        <div style="margin-bottom: 2rem;">
            <a href="recipe_detail.php?id=<?php echo $recipeId; ?>" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card" style="padding: 2rem;">
            <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-edit"></i> Edit Resep</h1>
            <p class="text-gray" style="margin-bottom: 2rem;">Update informasi resep Anda</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title" class="form-label">Judul Resep *</label>
                    <input type="text" id="title" name="title" class="form-input" required
                        value="<?php echo htmlspecialchars($recipe['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Deskripsi *</label>
                    <textarea id="description" name="description" class="form-textarea" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">URL Gambar</label>
                    <input type="url" id="image" name="image" class="form-input"
                        value="<?php echo htmlspecialchars($recipe['image']); ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="cooking_time" class="form-label">Waktu Memasak *</label>
                        <input type="text" id="cooking_time" name="cooking_time" class="form-input" required
                            value="<?php echo htmlspecialchars($recipe['cooking_time']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Kategori *</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="Makanan Utama" <?php echo $recipe['category'] === 'Makanan Utama' ? 'selected' : ''; ?>>Makanan Utama</option>
                            <option value="Camilan" <?php echo $recipe['category'] === 'Camilan' ? 'selected' : ''; ?>>Camilan</option>
                            <option value="Minuman" <?php echo $recipe['category'] === 'Minuman' ? 'selected' : ''; ?>>Minuman</option>
                            <option value="Dessert" <?php echo $recipe['category'] === 'Dessert' ? 'selected' : ''; ?>>Dessert</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="region" class="form-label">Region *</label>
                        <select id="region" name="region" class="form-select" required>
                            <option value="Jawa" <?php echo $recipe['region'] === 'Jawa' ? 'selected' : ''; ?>>Jawa</option>
                            <option value="Sumatera" <?php echo $recipe['region'] === 'Sumatera' ? 'selected' : ''; ?>>Sumatera</option>
                            <option value="Kalimantan" <?php echo $recipe['region'] === 'Kalimantan' ? 'selected' : ''; ?>>Kalimantan</option>
                            <option value="Sulawesi" <?php echo $recipe['region'] === 'Sulawesi' ? 'selected' : ''; ?>>Sulawesi</option>
                            <option value="Bali & Nusa Tenggara" <?php echo $recipe['region'] === 'Bali & Nusa Tenggara' ? 'selected' : ''; ?>>Bali & Nusa Tenggara</option>
                            <option value="Papua & Maluku" <?php echo $recipe['region'] === 'Papua & Maluku' ? 'selected' : ''; ?>>Papua & Maluku</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ingredients" class="form-label">Bahan-Bahan * <small class="text-gray">(Satu bahan per baris)</small></label>
                    <textarea id="ingredients" name="ingredients" class="form-textarea" required style="min-height: 200px;"><?php echo implode("\n", $ingredients); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="steps" class="form-label">Langkah-Langkah * <small class="text-gray">(Satu langkah per baris)</small></label>
                    <textarea id="steps" name="steps" class="form-textarea" required style="min-height: 250px;"><?php echo implode("\n", $steps); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="recipe_detail.php?id=<?php echo $recipeId; ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>