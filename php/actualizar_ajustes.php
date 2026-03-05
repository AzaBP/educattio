<?php
session_start();
include 'conexion.php';

// Seguridad: Si no está logueado, lo mandamos fuera
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../interfaces/inicio_sesion.html");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// Recogemos los datos del formulario de manera segura
$nuevo_email = trim($_POST['nuevo_email'] ?? '');
$pass_actual = $_POST['pass_actual'] ?? '';
$pass_nueva = $_POST['pass_nueva'] ?? '';
$pass_repetida = $_POST['pass_repetida'] ?? '';

// Validar que el email no esté vacío
if (empty($nuevo_email)) {
    header("Location: ../interfaces/ajustes.php?error=email_vacio");
    exit();
}

try {
    // CASO 1: El usuario QUIERE CAMBIAR LA CONTRASEÑA (ha rellenado el campo nueva contraseña)
    if (!empty($pass_nueva)) {
        
        // 1. Comprobamos que las contraseñas nuevas coinciden
        if ($pass_nueva !== $pass_repetida) {
            header("Location: ../interfaces/ajustes.php?error=pass_no_coincide");
            exit();
        }

        // 2. Buscamos la contraseña actual en la BD para verificar que es él de verdad
        $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id_usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Verificamos la contraseña actual
        if (!$user || !password_verify($pass_actual, $user['password'])) {
            header("Location: ../interfaces/ajustes.php?error=pass_incorrecta");
            exit();
        }

        // 4. Si todo es correcto, encriptamos la nueva y actualizamos Email + Contraseña
        $hash_nuevo = password_hash($pass_nueva, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET email = :email, password = :pass WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':email', $nuevo_email);
        $stmt->bindParam(':pass', $hash_nuevo);
        $stmt->bindParam(':id', $id_usuario);

    } 
    // CASO 2: El usuario SOLO QUIERE CAMBIAR EL EMAIL
    else {
        $sql = "UPDATE usuarios SET email = :email WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':email', $nuevo_email);
        $stmt->bindParam(':id', $id_usuario);
    }

    // Ejecutamos la consulta
    $stmt->execute();

    // Redirigimos con éxito
    header("Location: ../php/ajustes.php?exito=1");
    exit();

} catch (PDOException $e) {
    // Si hay un error, comprobamos si es porque el correo ya está registrado por otro usuario (Código de error 23000 en MySQL)
    if ($e->getCode() == 23000) {
        header("Location: ../php/ajustes.php?error=email_duplicado");
    } else {
        header("Location: ../php/ajustes.php?error=bd");
    }
    exit();
}
?>