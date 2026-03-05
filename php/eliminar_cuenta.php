<?php
session_start();
include 'conexion.php';

// Si no está logueado, lo mandamos fuera
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../interfaces/inicio_sesion.html");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

try {
    // 1. Borramos el usuario de la base de datos
    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id_usuario);
    $stmt->execute();

    // 2. Destruimos su sesión (lo deslogueamos de su navegador)
    session_destroy();

    // 3. Lo mandamos a la página principal de bienvenida
    header("Location: ../interfaces/inicio_web.html");
    exit();

} catch (PDOException $e) {
    // Si falla algo
    header("Location: ../interfaces/ajustes.php?error=bd");
    exit();
}
?>