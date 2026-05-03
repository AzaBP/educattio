<?php
session_start();
// Si el usuario ya ha iniciado sesión, lo redirigimos a su portal
if (isset($_SESSION['usuario_id'])) {
    header("Location: php/portal_inicio_usuario.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Tu rincón de evaluación</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="imagenes/dolphin.png">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="css/global.css?v=1.2">
    <link rel="stylesheet" href="css/inicio_web.css?v=1.2">
    
    <!-- Fuentes premium -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --font-sans: 'Outfit', sans-serif;
            --font-serif: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="auth-body">

    <main class="welcome-container">
        <!-- Logotipo / Título -->
        <h1 class="main-title">Educattio</h1>

        <!-- Eslogan -->
        <p class="tagline">Tu rincón de evaluación</p>
        
        <!-- Acciones principales -->
        <div class="button-group">
            <a href="php/login.php" class="btn btn-primary">Iniciar Sesión</a>
            <a href="php/registro_usuario.php" class="btn btn-secondary">Registrarse</a>
        </div>
    </main>

    <!-- Elemento decorativo: Profesor en la esquina -->
    <img src="imagenes/icons8-profesor-100.png" alt="Decoración Profesor" class="graphic-teacher-corner">

    <!-- Botón temporal para facilitar desarrollo (se puede quitar después) -->
    <a href="php/portal_inicio_usuario.php" class="temp-dashboard-btn">Ir al Dashboard (Debug)</a>

</body>
</html>
