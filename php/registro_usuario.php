<?php
session_start();
$error_general = isset($_SESSION['error_registro']) ? $_SESSION['error_registro'] : '';
$old_data = isset($_SESSION['old_registro']) ? $_SESSION['old_registro'] : [];
unset($_SESSION['error_registro'], $_SESSION['old_registro']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Crear Cuenta</title>
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css?v=1.2">
    <link rel="stylesheet" href="../css/registro_usuario.css?v=1.2">
    <!-- Fuentes premium -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
<main class="register-container">
    <div class="register-card">
        <header class="register-header">
            <h1 class="register-title">¡Bienvenido, profe!</h1>
            <p class="register-subtitle">Rellena el formulario para crear tu espacio educativo</p>
        </header>

        <?php if ($error_general): ?>
            <div class="error-global">
                <?php echo htmlspecialchars($error_general); ?>
            </div>
        <?php endif; ?>

        <form action="../php/registro.php" method="POST" class="register-form" id="registerForm">
            <div class="form-grid">
                <div class="input-wrapper">
                    <label for="name">Nombre Completo</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($old_data['name'] ?? ''); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="username">Nombre de Usuario</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($old_data['username'] ?? ''); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>" required>
                </div>
                <div class="input-wrapper">
                    <label for="phone">Teléfono (Opcional)</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($old_data['phone'] ?? ''); ?>">
                </div>
                <div class="input-wrapper">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-wrapper">
                    <label for="confirm-password">Repetir Contraseña</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                    <span id="password-error" class="error-message" style="display:none;">Las contraseñas no coinciden</span>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-minimal">Registrarse</button>
            </div>
            <div class="register-footer">
                <span>¿Ya tienes una cuenta? <a href="../php/login.php">Inicia Sesión aquí</a></span>
            </div>
        </form>
    </div>
</main>
<script src="../js/registro.js"></script>
</body>
</html>