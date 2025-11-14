<?php
require_once '../../config/functions.php';
require_once '../../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $conn = getDBConnection();

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $avatar = generateAvatarUrl($name);

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, avatar) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $avatar);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';

                // Auto login
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';
                $_SESSION['user_avatar'] = $avatar;

                header('Location: index.php');
                exit;
            } else {
                $error = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
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
    <title>Daftar - Nusa Bites</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div style="max-width: 1200px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
            <!-- Left side - Branding -->
            <div style="display: flex; flex-direction: column; align-items: center;">
                <a href="index.php" style="display: inline-flex; align-items: center; gap: 0.75rem; text-decoration: none; margin-bottom: 2rem;">
                    <div style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); padding: 1rem; border-radius: 1rem; box-shadow: var(--shadow-lg);">
                        <i class="fas fa-hat-chef" style="font-size: 3rem; color: white;"></i>
                    </div>
                </a>
                <h1 style="text-align: center; margin-bottom: 1rem;">Bergabung dengan Nusa Bites</h1>
                <p style="text-align: center; color: var(--color-text-gray); font-size: 1.125rem;">
                    Buat akun dan mulai berbagi resep masakan favorit Anda
                </p>

                <div style="margin-top: 2rem; width: 100%; max-width: 500px; height: 350px; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow-xl);">
                    <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwY29va2luZ3xlbnwxfHx8fDE3NjI0ODI5MDh8MA&ixlib=rb-4.1.0&q=80&w=1080"
                        alt="Cooking Together"
                        style="width: 100%; height: 100%; object-fit: cover;"
                        onerror="this.style.display='none'">
                </div>
            </div>

            <!-- Right side - Register Form -->
            <div class="card" style="padding: 2.5rem; box-shadow: var(--shadow-xl);">
                <div style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 0.5rem;">Buat Akun Baru</h2>
                    <p class="text-gray">
                        Sudah punya akun?
                        <a href="login.php" style="color: var(--color-primary); text-decoration: none; font-weight: 500;">
                            Masuk di sini
                        </a>
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>
                        <input type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            placeholder="Nama Anda"
                            required
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="nama@email.com"
                            required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div style="position: relative;">
                            <input type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Minimal 6 karakter"
                                required>
                            <button type="button"
                                onclick="togglePassword('password', 'toggleIcon1')"
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--color-text-gray);">
                                <i class="far fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock"></i> Konfirmasi Password
                        </label>
                        <div style="position: relative;">
                            <input type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Ulangi password"
                                required>
                            <button type="button"
                                onclick="togglePassword('confirm_password', 'toggleIcon2')"
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--color-text-gray);">
                                <i class="far fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                </form>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="index.php" style="color: var(--color-text-gray); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>