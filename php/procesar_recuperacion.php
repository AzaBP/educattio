<?php
session_start();

// Incluir la conexión PDO (ajusta ruta si es necesario, pero está en misma carpeta)
require_once 'conexion.php';   // <-- tu archivo con $conexion PDO

if(isset($_POST['reset_request'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // 1. Verificar si el email existe en la tabla 'usuarios'
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result) {
        // 2. Generar token seguro
        $token = bin2hex(random_bytes(32));
        date_default_timezone_set('America/Mexico_City'); // ajusta tu zona
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        // 3. Guardar token en password_resets
        $stmt_insert = $conexion->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
        $success = $stmt_insert->execute([
            ':email' => $email,
            ':token' => $token,
            ':expires_at' => $expires_at
        ]);

        if($success) {
            // 4. Construir enlace (nueva_contra.php debe estar también en la carpeta php/)
            $reset_link = "http://localhost/educattio/php/nueva_contra.php?token=" . urlencode($token);
            
            // 5. Enviar correo
            $to = $email;
            $subject = "Recupera tu contraseña - Educattio";
            $message = "Haz clic en el siguiente enlace para restablecer tu contraseña:\n\n" . $reset_link;
            $message .= "\n\nEste enlace es válido por 30 minutos.";
            $headers = "From: no-reply@educattio.local\r\n";
            
            if(mail($to, $subject, $message, $headers)) {
                $_SESSION['mensaje'] = "Te hemos enviado un enlace a tu correo.";
            } else {
                $_SESSION['error'] = "Error al enviar el correo. Intenta de nuevo.";
            }
        } else {
            $_SESSION['error'] = "Error al generar la solicitud.";
        }
    } else {
        // Seguridad: mensaje genérico
        $_SESSION['mensaje'] = "Si el correo existe, recibirás un enlace para recuperar tu contraseña.";
    }
    
    // Redirigir a login.php (que está en la raíz, un nivel arriba)
    header("Location: login.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>