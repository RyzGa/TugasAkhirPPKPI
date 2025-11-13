<?php
require_once '../config/functions.php';
require_once '../config/database.php';

requireLogin();

$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($recipeId === 0) {
    header('Location: ../index.php');
    exit;
}

$conn = getDBConnection();
$user = getCurrentUser();

// Check if user is admin or owner
$checkStmt = $conn->prepare("SELECT author_id FROM recipes WHERE id = ?");
$checkStmt->bind_param("i", $recipeId);
$checkStmt->execute();
$result = $checkStmt->get_result();
$recipe = $result->fetch_assoc();

if (!$recipe || ($user['role'] !== 'admin' && $user['id'] != $recipe['author_id'])) {
    header('Location: ../index.php');
    exit;
}

// Delete recipe (cascade will delete reviews and likes)
$deleteStmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
$deleteStmt->bind_param("i", $recipeId);
$deleteStmt->execute();

closeDBConnection($conn);

header('Location: ../index.php');
exit;
