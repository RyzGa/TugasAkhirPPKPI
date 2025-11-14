<?php
require_once 'config/functions.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Nusa Bites</title>
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

    <div class="container" style="padding: 3rem 1rem; max-width: 900px;">
        <div class="card" style="padding: 3rem;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <div style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-hat-chef" style="font-size: 3rem; color: white;"></i>
                </div>
                <h1>Tentang Nusa Bites</h1>
                <p class="text-lg text-gray">Platform Resep Masakan Nusantara</p>
            </div>

            <div style="max-width: 700px; margin: 0 auto;">
                <h2 style="margin-bottom: 1rem;">Misi Kami</h2>
                <p class="text-gray" style="margin-bottom: 2rem;">
                    Nusa Bites adalah platform yang didedikasikan untuk melestarikan dan membagikan kekayaan kuliner nusantara.
                    Kami percaya bahwa setiap resep adalah cerita, setiap hidangan adalah warisan budaya yang harus dijaga dan diteruskan
                    kepada generasi berikutnya.
                </p>

                <h2 style="margin-bottom: 1rem;">Apa yang Kami Tawarkan</h2>
                <div style="display: grid; gap: 1.5rem; margin-bottom: 2rem;">
                    <div style="display: flex; gap: 1rem;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 3rem; height: 3rem; background: var(--color-bg-light); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-book-open" style="color: var(--color-primary); font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Ribuan Resep Autentik</h3>
                            <p class="text-gray">Koleksi resep masakan tradisional dari berbagai daerah di Indonesia</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 3rem; height: 3rem; background: var(--color-bg-light); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-users" style="color: var(--color-primary); font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Komunitas Aktif</h3>
                            <p class="text-gray">Bergabung dengan ribuan pengguna yang berbagi pengalaman memasak</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div style="flex-shrink: 0;">
                            <div style="width: 3rem; height: 3rem; background: var(--color-bg-light); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-star" style="color: var(--color-primary); font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">Mudah Diikuti</h3>
                            <p class="text-gray">Setiap resep dilengkapi dengan langkah-langkah yang jelas dan mudah dipahami</p>
                        </div>
                    </div>
                </div>

                <h2 style="margin-bottom: 1rem;">Bergabunglah dengan Kami</h2>
                <p class="text-gray" style="margin-bottom: 1.5rem;">
                    Apakah Anda seorang koki rumahan yang ingin berbagi resep favorit? Atau mungkin Anda sedang mencari inspirasi
                    masakan untuk keluarga? Nusa Bites adalah tempat yang tepat untuk Anda!
                </p>

                <div style="text-align: center;">
                    <?php if (!$user): ?>
                        <a href="pages/auth/register.php" class="btn btn-primary" style="margin-right: 1rem;">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Jelajahi Resep
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>