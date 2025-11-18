<?php
// Halaman Logout
// Menghancurkan session user dan redirect ke homepage

session_start(); // Start session
session_destroy(); // Hapus semua data session (logout)
header('Location: ../../index.php'); // Redirect ke homepage
exit;
