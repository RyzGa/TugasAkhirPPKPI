<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- About Section -->
            <div class="footer-section">
                <h3>
                    <i class="fas fa-utensils"></i> Nusa Bites
                </h3>
                <p>Platform berbagi resep masakan Nusantara. Temukan dan bagikan resep favorit Anda dari berbagai daerah di Indonesia.</p>
                <div class="footer-social">
                    <a href="https://www.instagram.com/rizkyangga_7?utm_source=qr&igsh=MXZ1bG9neTJxeHBseA==" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/rizkyangga_7?utm_source=qr&igsh=MXZ1bG9neTJxeHBseA==" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.instagram.com/rizkyangga_7?utm_source=qr&igsh=MXZ1bG9neTJxeHBseA==" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.instagram.com/rizkyangga_7?utm_source=qr&igsh=MXZ1bG9neTJxeHBseA==" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h3>Menu Cepat</h3>
                <ul class="footer-links">
                    <li><a href="index.php" class="<?php echo isActivePage('index.php'); ?>"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="about.php" class="<?php echo isActivePage('about.php'); ?>"><i class="fas fa-info-circle"></i> Tentang Kami</a></li>
                    <li><a href="contact.php" class="<?php echo isActivePage('contact.php'); ?>"><i class="fas fa-envelope"></i> Kontak</a></li>
                    <?php if (isset($user)): ?>
                        <li><a href="add_recipe.php" class="<?php echo isActivePage('add_recipe.php'); ?>"><i class="fas fa-plus"></i> Tambah Resep</a></li>
                        <li><a href="profile.php" class="<?php echo isActivePage('profile.php'); ?>"><i class="fas fa-user"></i> Profil Saya</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-section">
                <h3>Kategori</h3>
                <ul class="footer-links">
                    <li><a href="index.php?categories[]=Makanan+Utama"><i class="fas fa-drumstick-bite"></i> Makanan Utama</a></li>
                    <li><a href="index.php?categories[]=Camilan"><i class="fas fa-cookie-bite"></i> Camilan</a></li>
                    <li><a href="index.php?categories[]=Minuman"><i class="fas fa-glass-martini-alt"></i> Minuman</a></li>
                </ul>
            </div>

            <!-- Regions -->
            <div class="footer-section">
                <h3>Region</h3>
                <ul class="footer-links">
                    <li><a href="index.php?regions[]=Jawa"><i class="fas fa-map-marker-alt"></i> Jawa</a></li>
                    <li><a href="index.php?regions[]=Sumatera"><i class="fas fa-map-marker-alt"></i> Sumatera</a></li>
                    <li><a href="index.php?regions[]=Kalimantan"><i class="fas fa-map-marker-alt"></i> Kalimantan</a></li>
                    <li><a href="index.php?regions[]=Sulawesi"><i class="fas fa-map-marker-alt"></i> Sulawesi</a></li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Nusa Bites. All rights reserved. Made with <i class="fas fa-heart" style="color: #ef4444;"></i> for Indonesian Cuisine</p>
        </div>
    </div>
</footer>
