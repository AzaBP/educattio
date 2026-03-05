<?php
session_start();
include 'conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_login = $_POST['usuario']; 
    $password_login = $_POST['password'];

    try {
        // 1. Buscamos si el usuario o email existen en la base de datos
        $sql = "SELECT id, nombre_usuario, password FROM usuarios WHERE nombre_usuario = :usr OR email = :usr";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usr', $usuario_login);
        $stmt->execute();

        // 2. ¿Existe alguien con ese nombre o correo?
        if ($stmt->rowCount() > 0) {
            $usuario_encontrado = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3. El usuario existe. Ahora comprobamos si la contraseña coincide
            if (password_verify($password_login, $usuario_encontrado['password'])) {
                
                // ¡Todo correcto! 
                $_SESSION['usuario_id'] = $usuario_encontrado['id'];
                $_SESSION['nombre_usuario'] = $usuario_encontrado['nombre_usuario'];

                header("Location: ../php/portal_inicio_usuario.php");
                exit();
                
            } else {
                // ERROR 1: La contraseña está mal. Lo devolvemos al login con una advertencia.
                header("Location: ../interfaces/inicio_sesion.html?error=pass");
                exit();
            }
        } else {
            // ERROR 2: El correo o usuario no existe en la base de datos.
            header("Location: ../interfaces/inicio_sesion.html?error=user");
            exit();
        }

    } catch (PDOException $e) {
        echo "Error en el inicio de sesión: " . $e->getMessage();
    }
}
?>