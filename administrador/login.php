<?php
session_start();
?>

<?php include '../header.php'; ?>

<main class="container">
    <section class="form-auth">
        <h2>Iniciar sesión</h2>

        <form action="/PHP/administrador/auth.php" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>

            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required>

            <button class="btn btn-primary" type="submit">Ingresar</button>
        </form>

        <p>¿No tienes cuenta? <a href="/PHP/register.php">Regístrate</a></p>
    </section>
</main>

<?php include '../footer.php'; ?>