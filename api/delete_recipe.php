<?php
// API: Delete Recipe
// Menghapus resep (hanya author atau admin yang bisa)

require_once '../config/functions.php';
require_once '../config/database.php';

requireLogin(); // Harus login

$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi recipe ID
if ($recipeId === 0) {
    header('Location: ../index.php');
    exit;
}

$conn = getDBConnection();
$user = getCurrentUser();

// Query: Cek author_id resep untuk validasi permission
$checkStmt = $conn->prepare("SELECT author_id FROM recipes WHERE id = ?");
$checkStmt->bind_param("i", $recipeId);
$checkStmt->execute();
$result = $checkStmt->get_result();
$recipe = $result->fetch_assoc();

// Validasi: hanya author atau admin yang boleh delete
if (!$recipe || ($user['role'] !== 'admin' && $user['id'] != $recipe['author_id'])) {
    header('Location: ../index.php');
    exit;
}

// Query: DELETE resep dari database
// Cascade akan otomatis menghapus reviews dan likes terkait
$deleteStmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
$deleteStmt->bind_param("i", $recipeId);
$deleteStmt->execute();

closeDBConnection($conn);

header('Location: ../index.php');
exit;
