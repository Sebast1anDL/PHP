<?php
session_start();
include 'db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $contrasena = trim($_POST['contrasena']);

    if (!$nombre || !$email || !$contrasena) {
        $mensaje = 'Todos los campos son obligatorios.';
    } else {
        // Validar email básico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'Correo electrónico inválido.';
        } else {
            // Revisar si el email ya existe
            $stmt = $conn->prepare('SELECT id FROM Usuario WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $mensaje = 'El email ya está registrado.';
            } else {
                $stmt->close();

                // Buscar rol de Cliente
                $rolDefault = 'Cliente';
                $rolId = null;
                $rolStmt = $conn->prepare('SELECT id FROM Rol WHERE nombre = ? LIMIT 1');
                $rolStmt->bind_param('s', $rolDefault);
                $rolStmt->execute();
                $rolResult = $rolStmt->get_result();

                if ($rolResult && $rolResult->num_rows > 0) {
                    $rolRow = $rolResult->fetch_assoc();
                    $rolId = $rolRow['id'];
                } else {
                    // Si no existe, crea rol Cliente
                    $tray = $conn->prepare('INSERT INTO Rol (nombre) VALUES (?)');
                    $tray->bind_param('s', $rolDefault);
                    $tray->execute();
                    $rolId = $conn->insert_id;
                    $tray->close();
                }

                $rolStmt->close();

                // Insertar usuario nuevo (mismo formato de contraseña en texto según código existente)
                $insert = $conn->prepare('INSERT INTO Usuario (nombre, email, contrasena, rol_id) VALUES (?, ?, ?, ?)');
                $insert->bind_param('sssi', $nombre, $email, $contrasena, $rolId);
                if ($insert->execute()) {
                    $mensaje = 'Registro exitoso. Ya puedes iniciar sesión.';
                    // redirigir a login si quieres
                    header('Location: administrador/login.php');
                    exit;
                } else {
                    $mensaje = 'Error al registrar usuario. Intenta de nuevo.';
                }
                $insert->close();
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<main class="container">
    <section class="form-auth">
        <h2>Crear cuenta</h2>

        <?php if ($mensaje): ?>
            <p style="color: red; font-weight: 600; text-align: center;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="POST" action="/PHP/register.php">
            <label for="nombre">Nombre de usuario</label>
            <input type="text" id="nombre" name="nombre" placeholder="Usuario" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>

            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required>

            <input type="hidden" name="rol_defecto" value="Cliente">

            <button class="btn btn-primary" type="submit">Registrar</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="/PHP/administrador/login.php">Inicia sesión</a></p>
    </section>
</main>

<?php include 'footer.php'; ?>