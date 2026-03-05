<?php
session_start();
include 'conexion.php';

// 1. SEGURIDAD: Comprobar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../interfaces/inicio_sesion.html");
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
    // 3. PREPARAR LA CONSULTA DE ACTUALIZACIÓN
    $sql = "UPDATE usuarios 
            SET nombre_completo = :nombre, 
                nombre_usuario = :usuario, 
                telefono = :telefono, 
                fecha_nacimiento = :fecha, 
                formacion_academica = :formacion, 
                experiencia_laboral = :experiencia 
            WHERE id = :id";
            
    $stmt = $conexion->prepare($sql);
    
    // 4. VINCULAR LOS PARÁMETROS
    $stmt->bindParam(':nombre', $nombre_completo);
    $stmt->bindParam(':usuario', $nombre_usuario);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':fecha', $fecha_nacimiento);
    $stmt->bindParam(':formacion', $formacion_academica);
    $stmt->bindParam(':experiencia', $experiencia_laboral);
    $stmt->bindParam(':id', $id_usuario);
    
    // 5. EJECUTAR LOS CAMBIOS
    $stmt->execute();

    // IMPORTANTE: Si ha cambiado su nombre de usuario, actualizamos la "memoria" de la sesión
    // para que no se le cierre la sesión ni haya errores de visualización.
    $_SESSION['nombre_usuario'] = $nombre_usuario;

    // 6. VOLVER A LA PÁGINA CON MENSAJE DE ÉXITO
    header("Location: ../php/perfil_usuario.php?exito=1");
    exit();

} catch (PDOException $e) {
    // Si hay un error (por ejemplo, si intenta ponerse un nombre de usuario que ya tiene otra persona)
    header("Location: ../php/perfil_usuario.php?error=1");
    exit();
}
?>