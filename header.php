<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Buen Comer - Restaurante</title>
    <link rel="stylesheet" href="/PHP/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header header-small">
        <div class="container header-nav-small">
            <div class="brand-small">
                <a href="/PHP/index.php"><img src="/PHP/images/Logo_final.png" alt="El Buen Comer Logo" class="logo-small"></a>
            </div>
            <h1 class="brand-name">El Buen Comer</h1>
            <div class="header-actions-small">
                <?php if (isset($_SESSION['nombre'])): ?>
                    <span class="user-welcome">Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    <a href="/PHP/administrador/logout.php" class="btn btn-outline">Cerrar sesión</a>
                <?php else: ?>
                    <a href="/PHP/register.php" class="btn btn-outline">Registrarse</a>
                    <a href="/PHP/administrador/login.php" class="btn btn-primary">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
