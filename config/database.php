<?php
// Konfigurasi database 
// Mendefinisikan konstanta untuk koneksi ke MySQL
define('DB_HOST', 'localhost');      // Host database
define('DB_USER', 'nusabites');      // Username database
define('DB_PASS', 'nusa');           // Password database
define('DB_NAME', 'nusabites');      // Nama database

// Fungsi untuk membuat koneksi ke database 
// Return: mysqli object untuk melakukan query
function getDBConnection()
{
    // Buat koneksi baru ke MySQL
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Cek apakah koneksi berhasil
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set character set ke utf8mb4 untuk support emoji dan karakter khusus
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Fungsi untuk menutup koneksi database -->
// Parameter: mysqli connection object -->
function closeDBConnection($conn)
{
    if ($conn) {
        $conn->close();
    }
}
