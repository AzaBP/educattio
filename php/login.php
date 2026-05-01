<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que la ruta sea correcta

$error_login = ''; // Variable para guardar mensaje de error de login

// Procesar el login cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $user = trim($_POST['usuario']);
    $pass = $_POST['password'];

    // Validar que no estén vacíos
    if (empty($user) || empty($pass)) {
        $error_login = 'Por favor, completa todos los campos.';
    }

    try {
        // Permitir login con nombre_usuario O email
        $sql = "SELECT id, password, nombre_usuario FROM usuarios WHERE nombre_usuario = :user OR email = :user";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':user' => $user]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($pass, $usuario['password'])) {
            // Login correcto
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

            // Redirige al portal (ajusta la ruta si es necesario)
            header("Location: portal_inicio_usuario.php");
            exit();
        } else {
            // Credenciales incorrectas
            $error_login = 'Usuario/email o contraseña incorrectos.';
        }
    } catch (PDOException $e) {
        // Error de base de datos - puedes logearlo internamente
        error_log("Error en login: " . $e->getMessage());
        $error_login = 'Error en la base de datos. Inténtalo más tarde.';
    }
}

// Si no es POST, se muestra la página del formulario (GET o cualquier otro método)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Iniciar Sesión</title>
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/inicio_web.css">
    <link rel="stylesheet" href="../css/inicio_sesion.css">
    <link rel="stylesheet" href="../css/global.css">
</head>
<body class="login-page">

<main class="login-container">
    <div class="login-card">
        <h1 class="login-title">Acceder</h1>
        <p class="login-subtitle">Introduce tus datos para continuar</p>

        <!-- Bloque de mensajes de error para login  -->
        <?php if (!empty($error_login)): ?>
            <div style="color: #d32f2f; background-color: #ffebee; border: 1px solid #ef5350; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($error_login); ?>
            </div>
        <?php endif; ?>

        <!-- Bloque de mensajes de recuperación de contraseña (desde sesión) -->
        <?php if(isset($_SESSION['mensaje'])): ?>
            <div style="color: #2e7d32; background-color: #e8f5e9; border: 1px solid #4caf50; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div style="color: #c62828; background-color: #ffebee; border: 1px solid #f44336; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de login -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="input-group">
                <label for="usuario">Correo Electrónico o Usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="usuario o correo@ejemplo.com" required>
            </div>

            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" name="login_submit" class="btn btn-primary btn-full">Entrar</button>
        </form>

        <div class="login-footer">
            <div class="separator">
                <span>o</span>
            </div>
            
            <div class="forgot-section">
                <h3 class="forgot-title">¿Olvidaste tu contraseña?</h3>
                <p class="forgot-text">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>
                <form action="procesar_recuperacion.php" method="POST" class="forgot-form">
                    <div class="input-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email" placeholder="usuario@ejemplo.com" required>
                    </div>
                    <button type="submit" name="reset_request" >Enviar enlace de recuperación</button>
                </form>
            </div>
            
            <div class="register-link">
                ¿No tienes cuenta? <a href="registro_usuario.php">Regístrate</a>
            </div>
        </div>
    </div>
</main>

<script>
    // Leer parámetro error de la URL (para login fallido)
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const mensajeErrorDiv = document.getElementById('mensaje-error');

    if (error === 'creds') {
        mensajeErrorDiv.textContent = 'Usuario/email o contraseña incorrectos. Inténtalo de nuevo.';
        mensajeErrorDiv.style.display = 'block';
    } else if (error === 'empty') {
        mensajeErrorDiv.textContent = 'Por favor, completa todos los campos.';
        mensajeErrorDiv.style.display = 'block';
    } else if (error === 'db') {
        mensajeErrorDiv.textContent = 'Error interno. Intenta más tarde.';
        mensajeErrorDiv.style.display = 'block';
    }
</script>

</body>
</html>