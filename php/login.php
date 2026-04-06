<?php
session_start(); // ¡Fundamental!
require_once 'conexion.php';

// Recoger datos del formulario (esto depende de tu HTML de login)
$user = $_POST['usuario'];
$pass = $_POST['password'];

try {
    $sql = "SELECT id, password FROM usuarios WHERE nombre_usuario = :user";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':user' => $user]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($pass, $usuario['password'])) {
        // LOGIN CORRECTO
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_usuario'] = $user;
        
        header("Location: portal_cursos.php");
    } else {
        header("Location: ../interfaces/inicio_sesion.html?error=1");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>