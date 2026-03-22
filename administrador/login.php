<?php session_start(); ?>

<h2>Login</h2>

<form action="auth.php" method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="contrasena" placeholder="Contraseña" required>
    <button type="submit">Ingresar</button>
</form>