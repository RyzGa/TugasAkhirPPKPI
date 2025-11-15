<?php
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
    $category = sanitizeInput($_POST['category']);
    $region = sanitizeInput($_POST['region']);

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; 

        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
        } else {
            $uploadDir = dirname(dirname(__DIR__)) . '/uploads/recipes/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $resizedPath = $uploadDir . 'resized_' . $filename;
                if (resizeImage($uploadPath, $resizedPath, 800, 600)) {
                    unlink($uploadPath); 
                    $image = 'uploads/recipes/resized_' . $filename;
                } else {
                    $image = 'uploads/recipes/' . $filename;
                }
            } else {
                $error = 'Gagal mengupload gambar.';
            }
        }
    }
    
    $ingredients = array_filter(array_map('trim', explode("\n", $_POST['ingredients'])));
    $steps = array_filter(array_map('trim', explode("\n", $_POST['steps'])));

    if (empty($error) && (empty($title) || empty($description) || count($ingredients) === 0 || count($steps) === 0)) {
        $error = 'Mohon lengkapi semua field yang diperlukan!';
    } elseif (empty($error)) {
        $conn = getDBConnection();

        $ingredientsJson = json_encode($ingredients);
        $stepsJson = json_encode($steps);

        $stmt = $conn->prepare("INSERT INTO recipes (title, description, image, author_id, author_name, author_avatar, cooking_time, category, region, ingredients, steps) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            $error = 'Database prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param("sssisssssss", $title, $description, $image, $user['id'], $user['name'], $user['avatar'], $cookingTime, $category, $region, $ingredientsJson, $stepsJson);

            if ($stmt->execute()) {
                $success = 'Resep berhasil ditambahkan!';
                $newRecipeId = $stmt->insert_id;
                header("Location: recipe_detail.php?id=$newRecipeId");
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

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

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

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="cooking_time" class="form-label">Waktu Memasak *</label>
                        <input type="text" id="cooking_time" name="cooking_time" class="form-input" required
                            placeholder="30 menit"
                            value="<?php echo isset($_POST['cooking_time']) ? htmlspecialchars($_POST['cooking_time']) : ''; ?>">
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

                <div class="form-group">
                    <label for="ingredients" class="form-label">Bahan-Bahan * <small class="text-gray">(Satu bahan per baris)</small></label>
                    <textarea id="ingredients" name="ingredients" class="form-textarea" required style="min-height: 200px;"
                        placeholder="500g daging sapi&#10;3 siung bawang putih&#10;5 siung bawang merah&#10;2 sdm kecap manis"><?php echo isset($_POST['ingredients']) ? htmlspecialchars($_POST['ingredients']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="steps" class="form-label">Langkah-Langkah * <small class="text-gray">(Satu langkah per baris)</small></label>
                    <textarea id="steps" name="steps" class="form-textarea" required style="min-height: 250px;"
                        placeholder="Potong daging menjadi ukuran sesuai selera&#10;Haluskan bawang putih dan bawang merah&#10;Tumis bumbu halus hingga harum"><?php echo isset($_POST['steps']) ? htmlspecialchars($_POST['steps']) : ''; ?></textarea>
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

    <script src="../../assets/js/dropdown.js"></script>
    <?php include '../../includes/footer.php'; ?>
</body>

</html>