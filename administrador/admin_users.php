<?php
session_start();
include '../db.php';
include 'template/header.php';

// Only admins
if ($_SESSION['rol'] !== 'Administrador') {
    die("Acceso denegado");
}

// CREATE USER
$mensaje = '';
if (isset($_POST['create'])) {

    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['contrasena']);
    $rol_id = $_POST['rol_id'];

    // Validación: email único
    $check = $conn->prepare('SELECT id FROM Usuario WHERE email = ?');
    $check->bind_param('s', $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $mensaje = 'El email ya está registrado. Usa otro email.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO Usuario (nombre, email, contrasena, rol_id) VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param('sssi', $nombre, $email, $password, $rol_id);
        if ($stmt->execute()) {
            $mensaje = 'Usuario creado con éxito.';
        } else {
            $mensaje = 'Error al crear usuario. Intenta nuevamente.';
        }
        $stmt->close();
    }

    $check->close();
}

// FETCH USERS
$users = $conn->query("
SELECT Usuario.*, Rol.nombre AS rol_nombre 
FROM Usuario 
JOIN Rol ON Usuario.rol_id = Rol.id
");
?>

<h2>👤 Gestión de Usuarios</h2>

<?php if (!empty($mensaje)): ?>
    <p style="color: #e74c3c; font-weight: 600;"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<!-- CREATE USER -->
<form method="POST">
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="contrasena" placeholder="Contraseña" required>

    <select name="rol_id">
        <?php
        $roles = $conn->query("SELECT * FROM Rol");
        while ($r = $roles->fetch_assoc()) {
            echo "<option value='{$r['id']}'>{$r['nombre']}</option>";
        }
        ?>
    </select>

    <button name="create">Crear Usuario</button>
</form>

<!-- USER LIST -->
<table border="1">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Email</th>
    <th>Rol</th>
</tr>

<?php while($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= $u['id'] ?></td>
    <td><?= $u['nombre'] ?></td>
    <td><?= $u['email'] ?></td>
    <td><?= $u['rol_nombre'] ?></td>
</tr>
<?php endwhile; ?>
</table>