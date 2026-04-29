<?php
session_start();
require_once 'conexion.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    $_SESSION['error'] = "Enlace inválido.";
    header("Location: login.php");
    exit();
}

date_default_timezone_set('America/Mexico_City');
$now = date('Y-m-d H:i:s');

$stmt = $conexion->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > :now");
$stmt->execute([':token' => $token, ':now' => $now]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    $_SESSION['error'] = "El enlace ha expirado o no es válido.";
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Nueva Contraseña</title>
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/inicio_web.css">
    <link rel="stylesheet" href="../css/inicio_sesion.css">
    <link rel="stylesheet" href="../css/global.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <h1 class="login-title">Nueva contraseña</h1>
            <p class="login-subtitle">Elige una contraseña segura para tu cuenta</p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="guardar_nueva_contra.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="input-group">
                    <label for="password">Nueva contraseña</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="reset_password" class="btn btn-full">Actualizar contraseña</button>
            </form>
            
            <div class="login-footer">
                <a href="login.php" class="back-link">← Volver al inicio de sesión</a>
            </div>
        </div>
    </div>

    <style>
        /* Estilo específico para mensajes de error en nueva_contra */
        .error-message {
            background-color: #ffebee;
            border-left: 4px solid #d32f2f;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #c62828;
            font-size: 0.9rem;
            text-align: left;
        }
        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: var(--accent-color);
        }
    </style>
</body>
</html>