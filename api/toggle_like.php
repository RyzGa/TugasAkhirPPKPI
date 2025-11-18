<?php
// API: Toggle Like/Unlike Recipe
// User dapat like atau unlike resep (toggle)

require_once '../config/functions.php';
require_once '../config/database.php';

header('Content-Type: application/json');

// Validasi user harus login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil recipe_id dari JSON request body
$data = json_decode(file_get_contents('php://input'), true);
$recipeId = isset($data['recipe_id']) ? (int)$data['recipe_id'] : 0;

if ($recipeId === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipe ID']);
    exit;
}

$conn = getDBConnection();
$user = getCurrentUser();

// Query: Cek apakah user sudah like resep ini
$checkStmt = $conn->prepare("SELECT id FROM liked_recipes WHERE user_id = ? AND recipe_id = ?");
$checkStmt->bind_param("ii", $user['id'], $recipeId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Query: DELETE like (unlike resep)
    $deleteStmt = $conn->prepare("DELETE FROM liked_recipes WHERE user_id = ? AND recipe_id = ?");
    $deleteStmt->bind_param("ii", $user['id'], $recipeId);
    $deleteStmt->execute();
    $message = 'Recipe removed from favorites';
} else {
    // Query: INSERT like (like resep)
    $insertStmt = $conn->prepare("INSERT INTO liked_recipes (user_id, recipe_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $user['id'], $recipeId);
    $insertStmt->execute();
    $message = 'Recipe added to favorites';
}

closeDBConnection($conn);

echo json_encode(['success' => true, 'message' => $message]);
