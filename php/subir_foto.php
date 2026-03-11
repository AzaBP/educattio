<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['nueva_foto'])) {
    $user_id = $_SESSION['usuario_id'];
    $file = $_FILES['nueva_foto'];
    
    // Validaciones básicas
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre_archivo = "user_" . $user_id . "_" . time() . "." . $extension;
    $ruta_destino = "../uploads/perfiles/" . $nombre_archivo;

    // Solo permitir imágenes
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($extension), $tipos_permitidos)) {
        if (move_uploaded_file($file['tmp_name'], $ruta_destino)) {
            // Actualizar base de datos
            $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            if ($stmt->execute([$nombre_archivo, $user_id])) {
                $_SESSION['foto_perfil'] = $nombre_archivo; // Actualizar sesión
                header("Location: ../interfaces/portal_inicio_usuario.php?success=1");
            }
        }
    }
}
?>