<?php
session_start();
include 'conexion.php';

// 1. SEGURIDAD: Comprobar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// 2. RECOGER LOS DATOS DEL FORMULARIO
// Usamos trim() para quitar espacios en blanco al principio y al final
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$formacion_academica = trim($_POST['formacion_academica'] ?? '');
$experiencia_laboral = trim($_POST['experiencia_laboral'] ?? '');

// Si la fecha viene vacía, la convertimos a NULL para que MySQL no dé error
if (empty($fecha_nacimiento)) {
    $fecha_nacimiento = NULL;
}

try {
    // 3. PROCESAR FOTO SI SE HA SUBIDO
    $foto_sql = "";
    $params = [
        ':nombre' => $nombre_completo,
        ':usuario' => $nombre_usuario,
        ':telefono' => $telefono,
        ':fecha' => $fecha_nacimiento,
        ':formacion' => $formacion_academica,
        ':experiencia' => $experiencia_laboral,
        ':id' => $id_usuario
    ];

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['foto_perfil'];
        $maxSize = 2 * 1024 * 1024;
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

        if ($archivo['size'] <= $maxSize && in_array($archivo['type'], $tiposPermitidos)) {
            $directorio = dirname(__DIR__) . '/uploads/perfil/';
            if (!is_dir($directorio)) mkdir($directorio, 0777, true);

            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $nombreUnico = $id_usuario . '_' . uniqid() . '.' . $extension;
            $rutaDestino = $directorio . $nombreUnico;
            
            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $foto_sql = ", foto_perfil = :foto";
                $params[':foto'] = 'uploads/perfil/' . $nombreUnico;
            }
        }
    }

    // 4. PREPARAR Y EJECUTAR LA CONSULTA DE ACTUALIZACIÓN
    $sql = "UPDATE usuarios 
            SET nombre_completo = :nombre, 
                nombre_usuario = :usuario, 
                telefono = :telefono, 
                fecha_nacimiento = :fecha, 
                formacion_academica = :formacion, 
                experiencia_laboral = :experiencia 
                $foto_sql
            WHERE id = :id";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    // IMPORTANTE: Si ha cambiado su nombre de usuario, actualizamos la sesión
    $_SESSION['nombre_usuario'] = $nombre_usuario;

    header("Location: perfil_usuario.php?exito=1" . (!empty($foto_sql) ? "&foto_ok=1" : ""));
    exit();

} catch (PDOException $e) {
    header("Location: perfil_usuario.php?error=1");
    exit();
}
?>