<?php
// Halaman Login
// Memproses autentikasi user dan membuat session

require_once '../../config/functions.php';
require_once '../../config/database.php';

$error = '';
$success = '';

// Proses form login saat method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan sanitize input dari form
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Validasi input tidak boleh kosong
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        // Koneksi ke database
        $conn = getDBConnection();
        
        // Query: SELECT user berdasarkan email
        $stmt = $conn->prepare("SELECT id, name, email, password, role, avatar FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Cek apakah user ditemukan
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password dengan hash
            if (password_verify($password, $user['password'])) {
                // Simpan data user ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_avatar'] = $user['avatar'];

                $success = 'Login berhasil! Selamat datang kembali.';
                // Redirect ke halaman utama
                header('Location: ../../index.php');
                exit;
            } else {
                $error = 'Email atau password salah!';
            }
        } else {
            $error = 'Email atau password salah!';
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
    <title>Masuk - Nusa Bites</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div style="max-width: 1200px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
            <!-- Left side - Branding -->
            <div style="display: flex; flex-direction: column; align-items: center;">
                <h1 style="text-align: center; margin-bottom: 1rem;">Selamat Datang di Nusa Bites</h1>
                <p style="text-align: center; color: var(--color-text-gray); font-size: 1.125rem;">
                    Jelajahi ribuan resep masakan nusantara dan berbagi kreasi kuliner Anda
                </p>

                <div style="margin-top: 2rem; width: 100%; max-width: 500px; height: 350px; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow-xl);">
                    <img src="../../assets/images/baner_login.jpg"
                        alt="Indonesian Cooking"
                        style="width: 100%; height: 100%; object-fit: cover;"
                        onerror="this.style.display='none'">
                </div>
            </div>

            <!-- Right side - Login Form -->
            <div class="card" style="padding: 2.5rem; box-shadow: var(--shadow-xl);">
                <div style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 0.5rem;">Masuk ke Akun Anda</h2>
                    <p class="text-gray">
                        Belum punya akun?
                        <a href="register.php" style="color: var(--color-primary); text-decoration: none; font-weight: 500;">
                            Daftar sekarang
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

                <form method="POST" action="login.php">
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
                                placeholder="Masukkan password"
                                required>
                            <button type="button"
                                onclick="togglePassword()"
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--color-text-gray);">
                                <i class="far fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>
                </form>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="../../index.php" style="color: var(--color-text-gray); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        console.log('🔐 Login page loaded');

        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Login page initialized');

            // Log form submission
            const loginForm = document.querySelector('form');
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    const email = document.getElementById('email')?.value;
                    console.log('🔑 Attempting login for email:', email);
                });
            }
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                console.log('👁️ Showing password');
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                console.log('🙈 Hiding password');
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>