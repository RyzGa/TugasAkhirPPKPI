<?php
// Tambah Resep - User dapat menambahkan resep baru
require_once '../../config/functions.php';
require_once '../../config/database.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $image = ''; // Default empty
    $cookingTime = sanitizeInput($_POST['cooking_time']);
    $servings = sanitizeInput($_POST['servings']);
    $category = sanitizeInput($_POST['category']);
    $region = sanitizeInput($_POST['region']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024;
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
        } else {
            $cloudinaryResult = uploadToCloudinary($_FILES['image']['tmp_name'], 'nusabites/recipes');

            if ($cloudinaryResult) {
                $image = $cloudinaryResult['secure_url'];
            } else {
                $error = 'Gagal mengupload gambar ke Cloudinary.';
            }
        }
    }

    $ingredients = isset($_POST['ingredients']) ? array_filter(array_map('trim', $_POST['ingredients'])) : [];
    $steps = isset($_POST['steps']) ? array_filter(array_map('trim', $_POST['steps'])) : [];

    if (empty($error) && (empty($title) || empty($description) || count($ingredients) === 0 || count($steps) === 0)) {
        $error = 'Mohon lengkapi semua field yang diperlukan!';
    } elseif (empty($error)) {
        $conn = getDBConnection();

        $ingredientsJson = json_encode($ingredients);
        $stepsJson = json_encode($steps);
        $stmt = $conn->prepare("INSERT INTO recipes (title, description, image, author_id, author_name, author_avatar, cooking_time, servings, category, region, ingredients, steps) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            $error = 'Database prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param("sssissssssss", $title, $description, $image, $user['id'], $user['name'], $user['avatar'], $cookingTime, $servings, $category, $region, $ingredientsJson, $stepsJson);

            if ($stmt->execute()) {
                $success = 'Resep berhasil ditambahkan!';
                $newRecipeId = $stmt->insert_id;
                header("Location: recipe_detail.php?id=$newRecipeId&success=added");
                exit;
            } else {
                $error = 'Terjadi kesalahan saat menambahkan resep: ' . $stmt->error;
            }
        }

        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Resep - Nusa Bites</title>
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
            <a href="../../index.php" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card" style="padding: 2rem;">
            <h1 style="margin-bottom: 0.5rem;"><i class="fas fa-plus-circle"></i> Tambah Resep Baru</h1>
            <p class="text-gray" style="margin-bottom: 2rem;">Bagikan resep masakan favorit Anda dengan komunitas Nusa Bites</p>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="form-label">Judul Resep *</label>
                    <input type="text" id="title" name="title" class="form-input" required
                        placeholder="Contoh: Rendang Daging Sapi Padang"
                        value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Deskripsi *</label>
                    <textarea id="description" name="description" class="form-textarea" required
                        placeholder="Ceritakan tentang resep ini..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">Gambar Resep</label>
                    <input type="file" id="image" name="image" class="form-input" accept="image/jpeg,image/png,image/webp,image/jpg">
                    <small class="text-gray">Upload gambar resep (JPG, PNG, atau WEBP, maksimal 5MB). Kosongkan jika tidak ada gambar.</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="cooking_time" class="form-label">Waktu Memasak *</label>
                        <input type="text" id="cooking_time" name="cooking_time" class="form-input" required
                            placeholder="30 menit"
                            value="<?php echo isset($_POST['cooking_time']) ? htmlspecialchars($_POST['cooking_time']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="servings" class="form-label">Porsi *</label>
                        <input type="text" id="servings" name="servings" class="form-input" required
                            placeholder="4 porsi"
                            value="<?php echo isset($_POST['servings']) ? htmlspecialchars($_POST['servings']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Kategori *</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Makanan Utama" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Makanan Utama') ? 'selected' : ''; ?>>Makanan Utama</option>
                            <option value="Camilan" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Camilan') ? 'selected' : ''; ?>>Camilan</option>
                            <option value="Minuman" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
                            <option value="Dessert" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Dessert') ? 'selected' : ''; ?>>Dessert</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="region" class="form-label">Region *</label>
                        <select id="region" name="region" class="form-select" required>
                            <option value="">Pilih Region</option>
                            <option value="Jawa" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Jawa') ? 'selected' : ''; ?>>Jawa</option>
                            <option value="Sumatera" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Sumatera') ? 'selected' : ''; ?>>Sumatera</option>
                            <option value="Kalimantan" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Kalimantan') ? 'selected' : ''; ?>>Kalimantan</option>
                            <option value="Sulawesi" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Sulawesi') ? 'selected' : ''; ?>>Sulawesi</option>
                            <option value="Bali & Nusa Tenggara" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Bali & Nusa Tenggara') ? 'selected' : ''; ?>>Bali & Nusa Tenggara</option>
                            <option value="Papua & Maluku" <?php echo (isset($_POST['region']) && $_POST['region'] === 'Papua & Maluku') ? 'selected' : ''; ?>>Papua & Maluku</option>
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
                        <div class="ingredient-item" style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="text" name="ingredients[]" class="form-input" placeholder="Masukkan bahan" required>
                            <button type="button" class="btn" onclick="removeIngredient(this)" style="background: transparent; color: var(--color-text-gray); padding: 0.5rem; min-width: auto;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
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
                        <div class="step-item" style="display: flex; gap: 0.5rem; align-items: center;">
                            <div style="width: 2.5rem; height: 2.5rem; border-radius: 50%; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; flex-shrink: 0;">
                                1
                            </div>
                            <input type="text" name="steps[]" class="form-input" placeholder="Masukkan langkah" required>
                            <button type="button" class="btn" onclick="removeStep(this)" style="background: transparent; color: var(--color-text-gray); padding: 0.5rem; min-width: auto;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="../../index.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Resep
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add Ingredient
        function addIngredient() {
            const list = document.getElementById('ingredientsList');
            const div = document.createElement('div');
            div.className = 'ingredient-item';
            div.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
            div.innerHTML = `
                <input type="text" name="ingredients[]" class="form-input" placeholder="Masukkan bahan" required>
                <button type="button" class="btn" onclick="removeIngredient(this)" style="background: transparent; color: var(--color-text-gray); padding: 0.5rem; min-width: auto;">
                    <i class="fas fa-times"></i>
                </button>
            `;
            list.appendChild(div);
        }

        // Remove Ingredient
        function removeIngredient(button) {
            const list = document.getElementById('ingredientsList');
            if (list.children.length > 1) {
                button.parentElement.remove();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Minimal harus ada 1 bahan!',
                    confirmButtonColor: '#ff6b6b'
                });
            }
        }

        // Add Step
        function addStep() {
            const list = document.getElementById('stepsList');
            const stepNumber = list.children.length + 1;
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
        }

        // Remove Step
        function removeStep(button) {
            const list = document.getElementById('stepsList');
            if (list.children.length > 1) {
                button.parentElement.remove();
                updateStepNumbers();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Minimal harus ada 1 langkah!',
                    confirmButtonColor: '#ff6b6b'
                });
            }
        }

        // Update Step Numbers
        function updateStepNumbers() {
            const steps = document.querySelectorAll('#stepsList .step-item');
            steps.forEach((step, index) => {
                const numberDiv = step.querySelector('div');
                numberDiv.textContent = index + 1;
            });
        }
    </script>
    <script src="../../assets/js/dropdown.js"></script>

    <?php if ($error): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>

    <?php if ($success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?php echo addslashes($success); ?>',
                confirmButtonColor: '#28a745'
            });
        </script>
    <?php endif; ?>

    <?php include '../../includes/footer.php'; ?>
</body>

</html>