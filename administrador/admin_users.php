<?php
session_start();
include '../db.php';
include 'template/header.php';

// Only admins
if ($_SESSION['rol'] !== 'Administrador') {
    die("Acceso denegado");
}

// CREATE USER
if (isset($_POST['create'])) {

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['contrasena'];
    $rol_id = $_POST['rol_id'];

    $stmt = $conn->prepare("
        INSERT INTO Usuario (nombre, email, contrasena, rol_id)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("sssi", $nombre, $email, $password, $rol_id);
    $stmt->execute();
}

// FETCH USERS
$users = $conn->query("
SELECT Usuario.*, Rol.nombre AS rol_nombre 
FROM Usuario 
JOIN Rol ON Usuario.rol_id = Rol.id
");
?>

<h2>👤 Gestión de Usuarios</h2>

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