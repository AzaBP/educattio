<?php
session_start();
require_once 'conexion.php'; // Asegúrate de que la ruta es correcta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar datos
    $nombre   = trim($_POST['name'] ?? '');
    $usuario  = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm-password'] ?? '';

    // Validar campos obligatorios
    $errores = [];

    if (empty($nombre)) $errores[] = "El nombre completo es obligatorio.";
    if (empty($usuario)) $errores[] = "El nombre de usuario es obligatorio.";
    if (empty($email)) $errores[] = "El correo electrónico es obligatorio.";
    
    // Validar email con FILTER_VALIDATE_EMAIL
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no tiene un formato válido.";
    } else {
        // Opcional: Verificar dominio real mediante DNS (puede ralentizar)
        $dominio = substr(strrchr($email, "@"), 1);
        if (!(checkdnsrr($dominio, "MX") || checkdnsrr($dominio, "A"))) {
            // Esto no es 100% fiable, pero ayuda. Puedes comentar esta línea si quieres
            $errores[] = "El dominio del correo no parece válido o no recibe emails.";
        }
    }

    if (empty($password)) $errores[] = "La contraseña es obligatoria.";
    if (strlen($password) < 8) $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    if ($password !== $confirm) $errores[] = "Las contraseñas no coinciden.";

    // Si hay errores, guardar en sesión y volver
    if (!empty($errores)) {
        $_SESSION['error_registro'] = implode("<br>", $errores);
        $_SESSION['old_registro'] = [
            'name' => $nombre,
            'username' => $usuario,
            'email' => $email,
            'phone' => $telefono
        ];
        header("Location: registro_usuario.php");
        exit();
    }

    try {
        // Verificar si el email ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $_SESSION['error_registro'] = "El correo electrónico ya está registrado.";
            $_SESSION['old_registro'] = [
                'name' => $nombre,
                'username' => $usuario,
                'email' => '',
                'phone' => $telefono
            ];
            header("Location: registro_usuario.php");
            exit();
        }

        // Verificar si el nombre de usuario ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre_usuario = :usuario");
        $stmt->execute([':usuario' => $usuario]);
        if ($stmt->fetch()) {
            $_SESSION['error_registro'] = "El nombre de usuario ya está en uso. Elige otro.";
            $_SESSION['old_registro'] = [
                'name' => $nombre,
                'username' => '',
                'email' => $email,
                'phone' => $telefono
            ];
            header("Location: registro_usuario.php");
            exit();
        }

        // Encriptar contraseña
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (nombre_completo, nombre_usuario, email, telefono, password) 
                VALUES (:nom, :usr, :em, :tel, :pass)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nom' => $nombre,
            ':usr' => $usuario,
            ':em' => $email,
            ':tel' => $telefono,
            ':pass' => $password_hash
        ]);

        $usuario_id = $conexion->lastInsertId();
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['nombre_usuario'] = $usuario;

        // (Opcional) Enviar correo de verificación - Nivel 4. Lo dejamos para después.
        // Por ahora, redirigimos al portal
        header("Location: portal_inicio_usuario.php");
        exit();

    } catch (PDOException $e) {
        // Error inesperado de base de datos
        $_SESSION['error_registro'] = "Error interno. Por favor, inténtalo más tarde.";
        $_SESSION['old_registro'] = [
            'name' => $nombre,
            'username' => $usuario,
            'email' => $email,
            'phone' => $telefono
        ];
        header("Location: registro_usuario.php");
        exit();
    }
} else {
    // Si alguien accede directamente a registro.php sin POST, redirigir al formulario
    header("Location: registro_usuario.php");
    exit();
}
?>