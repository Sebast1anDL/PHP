<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Restaurante</title>
    <style>
        body { font-family: Arial; margin: 0; }
        header {
            background: #333;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .user-info {
            font-size: 14px;
        }
    </style>
</head>

<body>

<header>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>

            <!-- Common -->
            <a href="/PHP/index.php">Inicio</a>

            <!-- Admin only -->
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
                <a href="/PHP/administrador/admin_index.php">CRUD Menu</a>
                <a href="/PHP/administrador/admin_users.php">Usuarios</a>
            <?php endif; ?>

        <?php endif; ?>
    </nav>

    <div class="user-info">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?= $_SESSION['nombre'] ?> (<?= $_SESSION['rol'] ?>)
            | <a href="/PHP/administrador/logout.php" style="color: #ff8080;">Logout</a>
        <?php else: ?>
            <a href="/PHP/administrador/login.php" style="color: lightgreen;">Login</a>
        <?php endif; ?>
    </div>
</header>