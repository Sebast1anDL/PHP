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
                    <a href="/PHP/favorites.php" class="favorites-link" title="Favoritos">
                        <svg width="24" height="48" viewBox="0 0 24 24" class="heart-icon">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                        </svg>
                    </a>
                    <a href="/PHP/administrador/logout.php" class="btn btn-outline">Cerrar sesión</a>
                <?php else: ?>
                    <a href="/PHP/register.php" class="btn btn-outline">Registrarse</a>
                    <a href="/PHP/administrador/login.php" class="btn btn-primary">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
