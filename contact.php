<?php
require_once 'config/functions.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - Nusa Bites</title>
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
                <a href="about.php" class="<?php echo isActivePage('about.php'); ?>">Tentang</a>
                <a href="contact.php" class="<?php echo isActivePage('contact.php'); ?>">Kontak</a>
                <?php if ($user): ?>
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

    <div class="container" style="padding: 3rem 1rem;">
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="card" style="padding: 3rem;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h1>Hubungi Kami</h1>
                    <p class="text-lg text-gray">Kami senang mendengar dari Anda!</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                    <div style="text-align: center; padding: 1.5rem; background: var(--color-bg-light); border-radius: 0.75rem;">
                        <i class="fas fa-envelope" style="font-size: 2rem; color: var(--color-primary); margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">Email</h3>
                        <p class="text-gray">info@nusabites.com</p>
                    </div>

                    <div style="text-align: center; padding: 1.5rem; background: var(--color-bg-light); border-radius: 0.75rem;">
                        <i class="fas fa-phone" style="font-size: 2rem; color: var(--color-primary); margin-bottom: 1rem;"></i>
                        <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">Telepon</h3>
                        <p class="text-gray">+62 812-3456-7890</p>
                    </div>
                </div>

                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" name="name" class="form-input" required placeholder="Nama Anda">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required placeholder="email@example.com">
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subjek</label>
                        <input type="text" id="subject" name="subject" class="form-input" required placeholder="Subjek pesan">
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Pesan</label>
                        <textarea id="message" name="message" class="form-textarea" required placeholder="Tulis pesan Anda di sini..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>