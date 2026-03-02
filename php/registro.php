<?php
include 'conexion.php'; // Importamos la conexión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger los datos del formulario (usando los 'name' del HTML)
    $nombre   = $_POST['name'];
    $usuario  = $_POST['username'];
    $email    = $_POST['email'];
    $telefono = $_POST['phone'];
    $password = $_POST['password'];

    // 2. IMPORTANTE: Encriptar la contraseña por seguridad
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // 3. Preparar la consulta SQL
        $sql = "INSERT INTO usuarios (nombre_completo, nombre_usuario, email, telefono, password) 
                VALUES (:nom, :usr, :em, :tel, :pass)";
        
        $stmt = $conexion->prepare($sql);

        // 4. Vincular los datos para evitar Inyección SQL (Hackeos)
        $stmt->bindParam(':nom', $nombre);
        $stmt->bindParam(':usr', $usuario);
        $stmt->bindParam(':em', $email);
        $stmt->bindParam(':tel', $telefono);
        $stmt->bindParam(':pass', $password_hash);

        // 5. Ejecutar
        if ($stmt->execute()) {
            echo "¡Usuario registrado con éxito! <a href='inicio_sesion.html'>Inicia sesión aquí</a>";
        }

    } catch (PDOException $e) {
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>