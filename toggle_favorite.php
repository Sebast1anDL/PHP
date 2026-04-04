<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['menu_id'])) {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
        header("Location: index.php");
        exit;
    } else {
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$menu_id = (int)$_GET['menu_id'];

// Check if exists
$sql_check = "SELECT 1 FROM Favoritos WHERE usuario_id = ? AND menu_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $menu_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

$exists = $result->num_rows > 0;

if ($exists) {
    // Remove
    $sql = "DELETE FROM Favoritos WHERE usuario_id = ? AND menu_id = ?";
} else {
    // Add
    $sql = "INSERT INTO Favoritos (usuario_id, menu_id) VALUES (?, ?)";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $menu_id);
$stmt->execute();
$stmt->close();
$stmt_check->close();

// Check again
$sql_check2 = "SELECT 1 FROM Favoritos WHERE usuario_id = ? AND menu_id = ?";
$stmt_check2 = $conn->prepare($sql_check2);
$stmt_check2->bind_param("ii", $user_id, $menu_id);
$stmt_check2->execute();
$result2 = $stmt_check2->get_result();
$is_fav = $result2->num_rows > 0;
$stmt_check2->close();

$conn->close();

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['is_fav' => $is_fav]);
?>