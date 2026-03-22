<?php
session_start();
include '../db.php';
include 'template/header.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    die("Acceso denegado");
}



// =========================
// DELETE
// =========================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM MenuItems WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin_index.php");
}

// =========================
// CREATE / UPDATE
// =========================
if (isset($_POST['save'])) {

    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];
    $imagen = $_POST['imagen'];

    if ($id == "") {
        // CREATE
        $stmt = $conn->prepare("INSERT INTO MenuItems (nombre, precio, categoria_id, imagen, creador_id) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("siis", $nombre, $precio, $categoria_id, $imagen);
    } else {
        // UPDATE
        $stmt = $conn->prepare("UPDATE MenuItems SET nombre=?, precio=?, categoria_id=?, imagen=? WHERE id=?");
        $stmt->bind_param("siisi", $nombre, $precio, $categoria_id, $imagen, $id);
    }

    $stmt->execute();
    header("Location: admin_index.php");
}

// =========================
// EDIT MODE
// =========================
$edit = false;
$nombre = "";
$precio = "";
$categoria_id = "";
$imagen = "";
$id = "";

if (isset($_GET['edit'])) {
    $edit = true;
    $id = $_GET['edit'];

    $result = $conn->query("SELECT * FROM MenuItems WHERE id=$id");
    $row = $result->fetch_assoc();

    $nombre = $row['nombre'];
    $precio = $row['precio'];
    $categoria_id = $row['categoria_id'];
    $imagen = $row['imagen'];
}

// =========================
// SEARCH
// =========================
$search = "";
$where = "";

if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $where = "WHERE MenuItems.nombre LIKE '%$search%'";
}

// =========================
// FETCH DATA
// =========================
$sql = "
SELECT MenuItems.*, Categoria.nombre AS categoria 
FROM MenuItems 
LEFT JOIN Categoria ON MenuItems.categoria_id = Categoria.id
$where
ORDER BY MenuItems.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Menu CRUD</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px;}
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left;}
        form { margin-bottom: 20px; }
        input, select { padding: 8px; margin: 5px;}
        button { padding: 8px 12px; }
        .edit { color: blue; }
        .delete { color: red; }
    </style>
</head>
<body>

<h2>🍽 Admin - Menu CRUD</h2>

<!-- ========================= -->
<!-- SEARCH -->
<!-- ========================= -->
<form method="GET">
    <input type="text" name="search" placeholder="Buscar..." value="<?= $search ?>">
    <button type="submit">Buscar</button>
</form>

<!-- ========================= -->
<!-- FORM -->
<!-- ========================= -->
<form method="POST">
    <input type="hidden" name="id" value="<?= $id ?>">

    <input type="text" name="nombre" placeholder="Nombre" required value="<?= $nombre ?>">
    <input type="number" name="precio" placeholder="Precio" required value="<?= $precio ?>">

    <select name="categoria_id">
        <?php
        $cats = $conn->query("SELECT * FROM Categoria");
        while ($c = $cats->fetch_assoc()) {
            $selected = ($categoria_id == $c['id']) ? "selected" : "";
            echo "<option value='{$c['id']}' $selected>{$c['nombre']}</option>";
        }
        ?>
    </select>

    <input type="text" name="imagen" placeholder="Imagen" value="<?= $imagen ?>">

    <button type="submit" name="save">
        <?= $edit ? "Actualizar" : "Agregar" ?>
    </button>
</form>

<!-- ========================= -->
<!-- TABLE -->
<!-- ========================= -->
<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Precio</th>
        <th>Categoría</th>
        <th>Acciones</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td>$<?= $row['precio'] ?></td>
        <td><?= $row['categoria'] ?></td>
        <td>
            <a class="edit" href="?edit=<?= $row['id'] ?>">Editar</a>
            |
            <a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Eliminar?')">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>