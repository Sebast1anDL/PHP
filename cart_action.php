<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$action  = $_POST['action'] ?? '';
$menu_id = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : 0;

function getOrCreateCart($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id FROM Carrito WHERE dueno_id = ? AND comprado = FALSE LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) return $row['id'];

    $stmt = $conn->prepare("INSERT INTO Carrito (dueno_id, total, comprado) VALUES (?, 0, FALSE)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();
    return $id;
}

function updateCartTotal($conn, $cart_id) {
    $stmt = $conn->prepare("
        UPDATE Carrito SET total = (
            SELECT COALESCE(SUM(m.precio * ci.cantidad), 0)
            FROM CarritoItems ci JOIN MenuItems m ON ci.menu_id = m.id
            WHERE ci.carrito_id = ?
        ) WHERE id = ?
    ");
    $stmt->bind_param("ii", $cart_id, $cart_id);
    $stmt->execute();
    $stmt->close();
}

function getCartCount($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(ci.cantidad), 0) AS cnt
        FROM Carrito c JOIN CarritoItems ci ON c.id = ci.carrito_id
        WHERE c.dueno_id = ? AND c.comprado = FALSE
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)$row['cnt'];
}

switch ($action) {

    case 'add':
        if ($menu_id <= 0) { echo json_encode(['error' => 'menu_id inválido']); exit; }
        $cart_id = getOrCreateCart($conn, $user_id);

        $stmt = $conn->prepare("SELECT cantidad FROM CarritoItems WHERE carrito_id = ? AND menu_id = ?");
        $stmt->bind_param("ii", $cart_id, $menu_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $new_qty = $row['cantidad'] + 1;
            $stmt = $conn->prepare("UPDATE CarritoItems SET cantidad = ? WHERE carrito_id = ? AND menu_id = ?");
            $stmt->bind_param("iii", $new_qty, $cart_id, $menu_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO CarritoItems (carrito_id, menu_id, cantidad) VALUES (?, ?, 1)");
            $stmt->bind_param("ii", $cart_id, $menu_id);
        }
        $stmt->execute();
        $stmt->close();
        updateCartTotal($conn, $cart_id);
        $_SESSION['cart_count'] = getCartCount($conn, $user_id);
        echo json_encode(['success' => true, 'cart_count' => $_SESSION['cart_count']]);
        break;

    case 'remove':
        if ($menu_id <= 0) { echo json_encode(['error' => 'menu_id inválido']); exit; }
        $stmt = $conn->prepare("SELECT id FROM Carrito WHERE dueno_id = ? AND comprado = FALSE LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) { echo json_encode(['success' => false]); exit; }
        $cart_id = $row['id'];

        $stmt = $conn->prepare("DELETE FROM CarritoItems WHERE carrito_id = ? AND menu_id = ?");
        $stmt->bind_param("ii", $cart_id, $menu_id);
        $stmt->execute();
        $stmt->close();
        updateCartTotal($conn, $cart_id);
        $_SESSION['cart_count'] = getCartCount($conn, $user_id);
        echo json_encode(['success' => true, 'cart_count' => $_SESSION['cart_count']]);
        break;

    case 'update':
        if ($menu_id <= 0) { echo json_encode(['error' => 'menu_id inválido']); exit; }
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $stmt = $conn->prepare("SELECT id FROM Carrito WHERE dueno_id = ? AND comprado = FALSE LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) { echo json_encode(['success' => false]); exit; }
        $cart_id = $row['id'];

        $stmt = $conn->prepare("UPDATE CarritoItems SET cantidad = ? WHERE carrito_id = ? AND menu_id = ?");
        $stmt->bind_param("iii", $qty, $cart_id, $menu_id);
        $stmt->execute();
        $stmt->close();
        updateCartTotal($conn, $cart_id);
        $_SESSION['cart_count'] = getCartCount($conn, $user_id);
        echo json_encode(['success' => true, 'cart_count' => $_SESSION['cart_count']]);
        break;

    case 'checkout':
        $stmt = $conn->prepare("SELECT id FROM Carrito WHERE dueno_id = ? AND comprado = FALSE LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) { echo json_encode(['success' => false, 'error' => 'Carrito vacío']); exit; }
        $cart_id = $row['id'];

        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM CarritoItems WHERE carrito_id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $cnt = (int)$stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();
        if ($cnt === 0) { echo json_encode(['success' => false, 'error' => 'Carrito vacío']); exit; }

        $stmt = $conn->prepare("UPDATE Carrito SET comprado = TRUE, fecha_compra = CURDATE() WHERE id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['cart_count'] = 0;
        echo json_encode(['success' => true, 'cart_count' => 0]);
        break;

    default:
        echo json_encode(['error' => 'Acción inválida']);
}

$conn->close();
