<?php
require_once 'config/functions.php';
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, name, email, password, role, avatar FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_avatar'] = $user['avatar'];

                $success = 'Login berhasil! Selamat datang kembali.';
                header('Location: index.php');
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
                <h1 style="text-align: center; margin-bottom: 1rem;">Selamat Datang di Nusa Bites</h1>
                <p style="text-align: center; color: var(--color-text-gray); font-size: 1.125rem;">
                    Jelajahi ribuan resep masakan nusantara dan berbagi kreasi kuliner Anda
                </p>

                <div style="margin-top: 2rem; width: 100%; max-width: 500px; height: 350px; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow-xl);">
                    <img src="https://images.unsplash.com/photo-1609847381390-2d71ed074efc?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZm9vZCUyMGNvb2tpbmd8ZW58MXx8fHwxNzYyNDgyOTA4fDA&ixlib=rb-4.1.0&q=80&w=1080"
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

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--color-border);">
                    <p style="text-align: center; color: var(--color-text-gray); font-size: 0.875rem; margin-bottom: 1rem;">
                        Demo Account:
                    </p>
                    <p style="text-align: center; color: var(--color-text-gray); font-size: 0.875rem;">
                        <strong>Admin:</strong> admin@nusabites.com / password<br>
                        <strong>User:</strong> siti@example.com / password
                    </p>
                </div>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="index.php" style="color: var(--color-text-gray); text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

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