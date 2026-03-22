<?php
session_start();
include '../db.php';

$email = $_POST['email'];
$password = $_POST['contrasena'];

$sql = "
SELECT Usuario.*, Rol.nombre AS rol_nombre 
FROM Usuario 
JOIN Rol ON Usuario.rol_id = Rol.id 
WHERE email = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // ⚠️ For now plain text (later: password_hash)
    if ($password === $user['contrasena']) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol_nombre'];

        // Redirect by role
        if ($user['rol_nombre'] === 'Administrador') {
            header("Location: admin_index.php");
        } else {
            header("Location: ../index.php");
        }
    } else {
        echo "Contraseña incorrecta";
    }
} else {
    echo "Usuario no encontrado";
}