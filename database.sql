-- Database untuk Nusa Bites
CREATE DATABASE IF NOT EXISTS nusabites CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nusabites;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel recipes
CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(500) DEFAULT NULL,
    author_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_avatar VARCHAR(255) DEFAULT NULL,
    cooking_time VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,
    region VARCHAR(50) NOT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    ingredients TEXT NOT NULL,
    steps TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_region (region),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    user_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_avatar VARCHAR(255) DEFAULT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel liked_recipes
CREATE TABLE IF NOT EXISTS liked_recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, recipe_id),
    INDEX idx_user (user_id),
    INDEX idx_recipe (recipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data admin default
INSERT INTO users (name, email, password, role, avatar) VALUES 
('Admin NusaBites', 'admin@nusabites.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Admin'),
('Siti Nurhaliza', 'siti@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti'),
('Budi Santoso', 'budi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Budi');

-- Password untuk semua user: password

-- Insert data resep
INSERT INTO recipes (title, description, image, author_id, author_name, author_avatar, cooking_time, category, region, rating, review_count, ingredients, steps) VALUES
('Nasi Goreng Spesial', 'Nasi goreng khas Indonesia dengan bumbu rempah yang kaya dan telur mata sapi.', 'https://images.unsplash.com/photo-1680674814945-7945d913319c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwbmFzaSUyMGdvcmVuZ3xlbnwxfHx8fDE3NjI0NzgwMzd8MA&ixlib=rb-4.1.0&q=80&w=1080', 2, 'Siti Nurhaliza', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti', '30 menit', 'Makanan Utama', 'Jawa', 4.8, 124, '["500g nasi putih dingin", "2 butir telur", "3 siung bawang putih", "5 siung bawang merah", "2 sdm kecap manis", "1 sdt garam", "1/2 sdt merica", "2 sdm minyak goreng", "Daun bawang secukupnya"]', '["Panaskan minyak dalam wajan, goreng telur orak-arik lalu sisihkan", "Tumis bawang putih dan bawang merah hingga harum", "Masukkan nasi putih, aduk rata", "Tambahkan kecap manis, garam, dan merica", "Masukkan telur orak-arik, aduk rata", "Tambahkan daun bawang, masak sebentar", "Sajikan selagi hangat"]'),

('Rendang Daging Sapi', 'Rendang autentik Padang dengan daging empuk dan bumbu yang meresap sempurna.', 'https://images.unsplash.com/photo-1620700668269-d3ad2a88f27e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwcmVuZGFuZ3xlbnwxfHx8fDE3NjI0ODI5MDZ8MA&ixlib=rb-4.1.0&q=80&w=1080', 3, 'Budi Santoso', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Budi', '2 jam', 'Makanan Utama', 'Sumatera', 4.9, 256, '["1kg daging sapi", "1 liter santan kelapa", "10 siung bawang merah", "5 siung bawang putih", "5 cabai merah", "2 batang serai", "3 lembar daun jeruk", "1 ruas lengkuas", "1 ruas jahe", "Garam secukupnya"]', '["Haluskan bumbu: bawang merah, bawang putih, cabai", "Tumis bumbu halus dengan serai, daun jeruk, lengkuas, jahe", "Masukkan daging, aduk hingga berubah warna", "Tuang santan, masak dengan api kecil", "Masak hingga santan menyusut dan bumbu meresap (sekitar 2 jam)", "Aduk sesekali agar tidak gosong", "Sajikan dengan nasi putih"]'),

('Sate Ayam Madura', 'Sate ayam dengan bumbu kacang yang gurih dan manis khas Madura.', 'https://images.unsplash.com/photo-1636301175218-6994458a4b0a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwc2F0ZSUyMHNhdGF5fGVufDF8fHx8MTc2MjQ4MjkwNnww&ixlib=rb-4.1.0&q=80&w=1080', 2, 'Siti Nurhaliza', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti', '45 menit', 'Makanan Utama', 'Jawa', 4.7, 89, '["500g daging ayam fillet", "Tusuk sate", "Untuk bumbu: 3 siung bawang putih", "2 sdm kecap manis", "1 sdt ketumbar", "Garam", "Untuk saus kacang: 200g kacang tanah", "3 siung bawang putih", "2 cabai merah", "Gula merah", "Air asam jawa"]', '["Potong daging ayam, marinasi dengan bumbu halus 30 menit", "Tusuk daging ayam ke tusuk sate", "Bakar di atas bara api sambil diolesi sisa bumbu", "Buat saus kacang: haluskan kacang, tumis bumbu, campur", "Sajikan sate dengan saus kacang, lontong, dan bawang goreng"]'),

('Gado-Gado Jakarta', 'Salad sayuran segar dengan saus kacang yang creamy dan lezat.', 'https://images.unsplash.com/photo-1707269561481-a4a0370a980a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZ2FkbyUyMGdhZG98ZW58MXx8fHwxNzYyNDgyOTA3fDA&ixlib=rb-4.1.0&q=80&w=1080', 3, 'Budi Santoso', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Budi', '25 menit', 'Makanan Utama', 'Jawa', 4.6, 67, '["Kangkung", "Kol", "Tauge", "Kentang", "Telur rebus", "Tahu goreng", "Tempe goreng", "Lontong", "Untuk saus: 200g kacang tanah", "Cabai rawit", "Gula merah", "Air asam jawa"]', '["Rebus sayuran: kangkung, kol, tauge", "Rebus kentang hingga empuk", "Goreng tahu dan tempe", "Buat saus kacang dengan menghaluskan kacang dan bumbu", "Tata sayuran, kentang, tahu, tempe, telur, dan lontong", "Siram dengan saus kacang", "Sajikan dengan kerupuk"]'),

('Bakso Malang', 'Bakso kenyal dengan kuah kaldu yang gurih dan tahu goreng crispy.', 'https://images.unsplash.com/photo-1696884422000-0fcd1f115c54?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwYmFrc298ZW58MXx8fHwxNzYyNDgyOTA3fDA&ixlib=rb-4.1.0&q=80&w=1080', 2, 'Siti Nurhaliza', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti', '1 jam', 'Makanan Utama', 'Jawa', 4.8, 156, '["500g daging sapi giling", "100g tepung tapioka", "3 siung bawang putih", "1 butir telur", "Es batu", "Garam", "Merica", "Kaldu sapi", "Mie kuning", "Tahu goreng", "Daun seledri", "Bawang goreng"]', '["Haluskan daging sapi dengan es batu dan bumbu", "Tambahkan tepung tapioka dan telur, aduk rata", "Bentuk adonan menjadi bulat-bulat", "Rebus bakso dalam air mendidih hingga mengapung", "Buat kuah kaldu sapi yang gurih", "Rebus mie kuning", "Sajikan bakso dengan mie, kuah, tahu goreng, seledri, dan bawang goreng"]'),

('Martabak Manis', 'Martabak tebal dengan topping coklat, keju, dan kacang yang melimpah.', 'https://images.unsplash.com/photo-1706922122195-a1d670210618?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwbWFydGFiYWt8ZW58MXx8fHwxNzYyNDgyOTA4fDA&ixlib=rb-4.1.0&q=80&w=1080', 3, 'Budi Santoso', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Budi', '40 menit', 'Camilan', 'Sumatera', 4.5, 92, '["250g tepung terigu", "2 butir telur", "300ml susu cair", "50g gula pasir", "1 sdt ragi instant", "1/2 sdt soda kue", "Topping: meses coklat", "Keju parut", "Kacang tanah cincang", "Margarin"]', '["Campur tepung, gula, ragi, telur, dan susu", "Aduk hingga rata, diamkan 1 jam", "Tambahkan soda kue, aduk rata", "Panaskan wajan martabak", "Tuang adonan, masak hingga berlubang", "Beri topping: margarin, meses, keju, kacang", "Lipat dan potong, sajikan hangat"]'),

('Es Teler Segar', 'Minuman segar dengan campuran buah alpukat, kelapa muda, dan santan.', 'https://images.unsplash.com/photo-1649090909560-6c71b1aeaa7d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZXMlMjB0ZWxlcnxlbnwxfHx8fDE3NjI0ODI5MDh8MA&ixlib=rb-4.1.0&q=80&w=1080', 2, 'Siti Nurhaliza', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti', '15 menit', 'Minuman', 'Jawa', 4.7, 78, '["1 buah alpukat", "1 buah kelapa muda", "100g nangka matang", "200ml santan kental", "100g gula pasir", "Es batu", "Susu kental manis"]', '["Potong dadu alpukat, kelapa muda, dan nangka", "Buat sirup gula dengan merebus gula dan air", "Campur santan dengan sedikit garam", "Tata buah dalam gelas", "Tuang sirup gula", "Tambahkan santan dan susu kental manis", "Beri es batu", "Sajikan dingin"]'),

('Soto Ayam Lamongan', 'Soto ayam dengan kuah bening yang segar dan bumbu koya yang harum.', 'https://images.unsplash.com/photo-1609847381390-2d71ed074efc?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbmRvbmVzaWFuJTIwZm9vZCUyMGNvb2tpbmd8ZW58MXx8fHwxNzYyNDgyOTA4fDA&ixlib=rb-4.1.0&q=80&w=1080', 3, 'Budi Santoso', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Budi', '50 menit', 'Makanan Utama', 'Jawa', 4.6, 134, '["1 ekor ayam kampung", "3 lembar daun jeruk", "2 batang serai", "1 ruas lengkuas", "5 siung bawang putih", "8 siung bawang merah", "2 sdt ketumbar", "1 sdt kunyit", "Garam", "Merica", "Untuk bumbu koya: kacang tanah goreng", "Bawang putih goreng", "Daun seledri", "Bawang goreng"]', '["Rebus ayam dengan serai, lengkuas, daun jeruk hingga empuk", "Angkat ayam, suwir-suwir dagingnya", "Haluskan bumbu: bawang merah, bawang putih, ketumbar, kunyit", "Tumis bumbu halus hingga harum", "Masukkan bumbu tumis ke kuah kaldu", "Buat bumbu koya dengan menghaluskan kacang dan bawang putih goreng", "Sajikan soto dengan ayam suwir, tauge, kubis, telur, bihun", "Taburi bumbu koya, seledri, dan bawang goreng"]');

-- Insert sample reviews
INSERT INTO reviews (recipe_id, user_id, author_name, author_avatar, rating, comment) VALUES
(1, 2, 'Siti Nurhaliza', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti', 5, 'Resepnya sangat mudah diikuti dan hasilnya enak sekali! Keluarga saya sangat suka.'),
(1, 3, 'Budi Santoso', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Budi', 4, 'Enak, tapi saya tambahkan sedikit cabai untuk rasa lebih pedas.'),
(2, 2, 'Siti Nurhaliza', 'https://api.dicebear.com/7.x/avataaars/svg?seed=Siti', 5, 'Rendang terenak yang pernah saya coba! Bumbunya meresap sempurna.');
