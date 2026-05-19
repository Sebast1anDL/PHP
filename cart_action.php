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

        // Obtener ítems para el email y el historial (antes de marcar como comprado)
        $stmt = $conn->prepare("
            SELECT ci.menu_id, m.nombre, m.precio, ci.cantidad
            FROM CarritoItems ci JOIN MenuItems m ON ci.menu_id = m.id
            WHERE ci.carrito_id = ?
            ORDER BY m.nombre
        ");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($order_items)) { echo json_encode(['success' => false, 'error' => 'Carrito vacío']); exit; }

        $order_total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $order_items));

        // Obtener datos del usuario para el email
        $stmt = $conn->prepare("SELECT nombre, email FROM Usuario WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Marcar carrito como comprado
        $today = date('Y-m-d');
        $stmt = $conn->prepare("UPDATE Carrito SET comprado = TRUE, fecha_compra = CURDATE() WHERE id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $stmt->close();

        // Guardar en historial (snapshot de nombres y precios)
        $stmt = $conn->prepare("INSERT INTO HistorialPedidos (usuario_id, fecha_pedido, total) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $today, $order_total);
        $stmt->execute();
        $pedido_id = $conn->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO HistorialItems (pedido_id, menu_id, nombre_item, precio_unitario, cantidad) VALUES (?, ?, ?, ?, ?)");
        foreach ($order_items as $item) {
            $stmt->bind_param("iisii", $pedido_id, $item['menu_id'], $item['nombre'], $item['precio'], $item['cantidad']);
            $stmt->execute();
        }
        $stmt->close();

        $_SESSION['cart_count'] = 0;

        // Enviar email de confirmación (best-effort: no interrumpe si falla)
        if ($user_data && !empty($user_data['email'])) {
            require_once __DIR__ . '/mailer.php';
            sendOrderEmail($user_data['email'], $user_data['nombre'], $order_items, $order_total, $today);
        }

        echo json_encode(['success' => true, 'cart_count' => 0]);
        break;

    default:
        echo json_encode(['error' => 'Acción inválida']);
}

$conn->close();
