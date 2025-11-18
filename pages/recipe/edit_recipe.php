<?php
require_once '../../config/functions.php';
require_once '../../config/database.php';

requireLogin();

$user = getCurrentUser();
$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($recipeId === 0) {
    header('Location: ../../index.php');
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
    header('Location: ../../index.php');
    exit;
}

// Check permission
if ($user['role'] !== 'admin' && $user['id'] != $recipe['author_id']) {
    header('Location: ../../index.php');
    exit;
}

// Parse ingredients and steps
$ingredients = json_decode($recipe['ingredients'], true);
$steps = json_decode($recipe['steps'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $image = $recipe['image']; // Keep existing image by default
    $cookingTime = sanitizeInput($_POST['cooking_time']);
    $category = sanitizeInput($_POST['category']);
    $region = sanitizeInput($_POST['region']);

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
        } else {
            // Upload to Cloudinary
            $cloudinaryResult = uploadToCloudinary($_FILES['image']['tmp_name'], 'nusabites/recipes');

            if ($cloudinaryResult) {
                // Delete old image from Cloudinary if exists
                if (!empty($recipe['image']) && strpos($recipe['image'], 'cloudinary.com') !== false) {
                    preg_match('/\/([^\/]+)\.(jpg|png|webp)$/', $recipe['image'], $matches);
                    if (isset($matches[1])) {
                        deleteFromCloudinary('nusabites/recipes/' . $matches[1]);
                    }
                }

                $image = $cloudinaryResult['secure_url'];
            } else {
                $error = 'Gagal mengupload gambar ke Cloudinary.';
            }
        }
    }

    // Proses ingredients dan steps sebagai array
    $newIngredients = isset($_POST['ingredients']) ? array_filter(array_map('trim', $_POST['ingredients'])) : [];
    $newSteps = isset($_POST['steps']) ? array_filter(array_map('trim', $_POST['steps'])) : [];

    if (empty($error) && (empty($title) || empty($description) || count($newIngredients) === 0 || count($newSteps) === 0)) {
        $error = 'Mohon lengkapi semua field yang diperlukan!';
    } elseif (empty($error)) {
        $ingredientsJson = json_encode(array_values($newIngredients));
        $stepsJson = json_encode(array_values($newSteps));

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

            <form method="POST" action="" enctype="multipart/form-data">
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
                    <label for="image" class="form-label">Gambar Resep</label>
                    <?php if (!empty($recipe['image'])): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <small class="text-gray">Gambar saat ini: <?php echo basename($recipe['image']); ?></small>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="form-input" accept="image/jpeg,image/png,image/webp,image/jpg">
                    <small class="text-gray">Upload gambar baru (JPG, PNG, atau WEBP, maksimal 5MB). Kosongkan jika tidak ingin mengubah gambar.</small>
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

                <!-- Bahan-Bahan Section -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <label class="form-label" style="margin: 0;">Bahan-Bahan</label>
                        <button type="button" class="btn btn-secondary" onclick="addIngredient()" style="padding: 0.5rem 1rem;">
                            <i class="fas fa-plus"></i> Tambah Bahan
                        </button>
                    </div>
                    <div id="ingredientsList" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($ingredients as $ingredient): ?>
                            <div class="ingredient-item" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="text" name="ingredients[]" class="form-input" placeholder="Masukkan bahan" value="<?php echo htmlspecialchars($ingredient); ?>" required>
                                <button type="button" class="btn" onclick="removeIngredient(this)" style="background: transparent; color: var(--color-text-gray); padding: 0.5rem; min-width: auto;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Langkah-Langkah Section -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <label class="form-label" style="margin: 0;">Langkah-Langkah</label>
                        <button type="button" class="btn btn-secondary" onclick="addStep()" style="padding: 0.5rem 1rem;">
                            <i class="fas fa-plus"></i> Tambah Langkah
                        </button>
                    </div>
                    <div id="stepsList" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($steps as $index => $step): ?>
                            <div class="step-item" style="display: flex; gap: 0.5rem; align-items: center;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0;">
                                    <?php echo $index + 1; ?>
                                </div>
                                <input type="text" name="steps[]" class="form-input" placeholder="Masukkan langkah" value="<?php echo htmlspecialchars($step); ?>" required>
                                <button type="button" class="btn" onclick="removeStep(this)" style="background: transparent; color: var(--color-text-gray); padding: 0.5rem; min-width: auto;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
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

    <script>
        // Log page load
        console.log('✏️ Edit recipe page loaded');
        console.log('🍽️ Editing recipe ID:', <?php echo $recipeId; ?>);

        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Edit recipe page initialized');
            console.log('📊 Current recipe data:', {
                title: '<?php echo addslashes($recipe['title']); ?>',
                ingredientsCount: <?php echo count($ingredients); ?>,
                stepsCount: <?php echo count($steps); ?>
            });

            // Log form submission
            const recipeForm = document.querySelector('form');
            if (recipeForm) {
                recipeForm.addEventListener('submit', function() {
                    const formData = new FormData(this);
                    console.log('💾 Saving recipe changes');
                    console.log('📋 Updated form data:', {
                        title: formData.get('title'),
                        category: formData.get('category'),
                        region: formData.get('region'),
                        ingredientsCount: formData.getAll('ingredients[]').length,
                        stepsCount: formData.getAll('steps[]').length
                    });
                });
            }

            // Log image selection
            const imageInput = document.querySelector('input[type="file"]');
            if (imageInput) {
                imageInput.addEventListener('change', function() {
                    console.log('🖼️ New image selected:', this.files[0]?.name, `(${(this.files[0]?.size / 1024).toFixed(2)} KB)`);
                });
            }
        });

        // Add Ingredient
        function addIngredient() {
            console.log('➕ Adding new ingredient field');
            const list = document.getElementById('ingredientsList');
            const item = document.createElement('div');
            item.className = 'ingredient-item';
            item.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
            item.innerHTML = `
                <input type="text" name="ingredients[]" class="form-input" placeholder="Masukkan bahan" required>
                <button type="button" class="btn" onclick="removeIngredient(this)" style="background: transparent; color: var(--color-text-gray); padding: 0.5rem; min-width: auto;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            list.appendChild(item);
            console.log('✅ Ingredient field added. Total:', list.children.length);
        }

        // Remove Ingredient
        function removeIngredient(button) {
            const list = document.getElementById('ingredientsList');
            if (list.children.length > 1) {
                console.log('➖ Removing ingredient field');
                button.parentElement.remove();
                console.log('✅ Ingredient removed. Remaining:', list.children.length);
            } else {
                console.log('⚠️ Cannot remove last ingredient');
                alert('Minimal harus ada 1 bahan!');
            }
        }

        // Add Step
        function addStep() {
            const list = document.getElementById('stepsList');
            const stepNumber = list.children.length + 1;
            console.log('➕ Adding new step field:', stepNumber);
            const item = document.createElement('div');
            item.className = 'step-item';
            item.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
            item.innerHTML = `
                <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0;">
                    ${stepNumber}
                </div>
                <input type="text" name="steps[]" class="form-input" placeholder="Masukkan langkah" required>
                <button type="button" class="btn" onclick="removeStep(this)" style="background: transparent; color: var(--color-text-gray); padding: 0.5rem; min-width: auto;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            list.appendChild(item);
            console.log('✅ Step field added. Total:', list.children.length);
        }

        // Remove Step
        function removeStep(button) {
            const list = document.getElementById('stepsList');
            if (list.children.length > 1) {
                console.log('➖ Removing step field');
                button.parentElement.remove();
                updateStepNumbers();
                console.log('✅ Step removed. Remaining:', list.children.length);
            } else {
                console.log('⚠️ Cannot remove last step');
                alert('Minimal harus ada 1 langkah!');
            }
        }

        // Update Step Numbers
        function updateStepNumbers() {
            console.log('🔢 Updating step numbers');
            const steps = document.querySelectorAll('#stepsList .step-item');
            steps.forEach((step, index) => {
                const numberDiv = step.querySelector('div');
                numberDiv.textContent = index + 1;
            });
            console.log('✅ Step numbers updated');
        }
    </script>
    <script src="../../assets/js/dropdown.js"></script>
    <?php include '../../includes/footer.php'; ?>
</body>

</html>