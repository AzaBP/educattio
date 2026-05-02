<?php
session_start();
// Asegúrate de que la ruta a conexion.php es correcta según tus carpetas
include 'conexion.php'; 

// 1. SEGURIDAD: Si no está logueado, lo echamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// 2. OBTENER LOS DATOS ACTUALES DEL USUARIO DE LA BASE DE DATOS
try {
    // Pedimos el nombre completo, el de usuario, el email y la foto
    $sql = "SELECT nombre_completo, nombre_usuario, email, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id_usuario);
    $stmt->execute();
    
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Por si la cuenta fue borrada mientras estaba logueado
    if (!$datos_usuario) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error al cargar los datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Ajustes de Cuenta</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css"> 
    <link rel="stylesheet" href="../css/ajustes.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-layout">
        
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="page-header">
                <h1>Ajustes de Cuenta</h1>
                <p>Gestiona tu seguridad, notificaciones y cuenta.</p>
            </header>

            <div class="settings-container">

                <?php if(isset($_GET['exito'])): ?>
                    <div style="background-color: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; text-align: center;">
                        <i class="fas fa-check-circle"></i> ¡Cambios guardados correctamente!
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['error'])): ?>
                    <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; text-align: center;">
                        <i class="fas fa-exclamation-circle"></i> 
                        <?php 
                            if ($_GET['error'] == 'pass_incorrecta') {
                                echo 'La contraseña actual no es correcta.';
                            } elseif ($_GET['error'] == 'pass_no_coincide') {
                                echo 'Las contraseñas nuevas no coinciden.';
                            } elseif ($_GET['error'] == 'email_duplicado') {
                                echo 'Ese correo electrónico ya está registrado en otra cuenta.';
                            } elseif ($_GET['error'] == 'email_vacio') {
                                echo 'El correo electrónico no puede estar vacío.';
                            } else {
                                echo 'Hubo un error al intentar guardar los cambios.';
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="../php/actualizar_ajustes.php" method="POST">
                    <section class="card settings-card">
                        <div class="card-header">
                            <div class="icon-box color-blue"><i class="fas fa-shield-alt"></i></div>
                            <h3>Seguridad y Acceso</h3>
                        </div>
                        
                        <div class="settings-form">
                            <div class="current-email-display">
                                <div>
                                    <p>Correo electrónico actual</p>
                                    <strong><?php echo htmlspecialchars($datos_usuario['email']); ?></strong>
                                </div>
                                <span class="badge-verified"><i class="fas fa-check-circle"></i> Verificado</span>
                            </div>

                            <div class="input-group">
                                <label>Cambiar Correo Electrónico</label>
                                <input type="email" name="nuevo_email" value="<?php echo htmlspecialchars($datos_usuario['email']); ?>" required>
                            </div>

                            <h4 style="margin-top: 2rem; margin-bottom: 1rem; color: #555;">Cambiar Contraseña (Opcional)</h4>
                            
                            <div class="form-row three-cols">
                                <div class="input-group">
                                    <label>Contraseña Actual</label>
                                    <input type="password" name="pass_actual" placeholder="Requerida para cambiar contraseña">
                                </div>
                                <div class="input-group">
                                    <label>Nueva Contraseña</label>
                                    <input type="password" name="pass_nueva" placeholder="Mínimo 8 caracteres">
                                </div>
                                <div class="input-group">
                                    <label>Repetir Nueva Contraseña</label>
                                    <input type="password" name="pass_repetida" placeholder="Confirma la nueva contraseña">
                                </div>
                            </div>
                            
                            <div style="text-align: right; margin-top: 1rem;">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </section>
                </form>

                <section class="card settings-card">
                    <div class="card-header">
                        <div class="icon-box color-blue"><i class="fas fa-bell"></i></div>
                        <h3>Notificaciones</h3>
                    </div>
                    <div class="toggles-list">
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <h4>Correos Electrónicos</h4>
                                <p>Recibir resúmenes semanales.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="slider round"></span></label>
                        </div>
                    </div>
                </section>

                <section class="card settings-card danger-zone">
                    <div class="card-header">
                        <div class="icon-box color-red"><i class="fas fa-exclamation-triangle"></i></div>
                        <h3 class="text-danger">Zona de Peligro</h3>
                    </div>
                    <div class="danger-content">
                        <div class="danger-text">
                            <h4>Eliminar Cuenta</h4>
                            <p>Esta acción no se puede deshacer. Se borrarán permanentemente todos tus cursos, clases y alumnos.</p>
                        </div>
                        
                        <form action="../php/eliminar_cuenta.php" method="POST" onsubmit="return confirm('⚠️ ¿Estás COMPLETAMENTE SEGURO de que deseas eliminar tu cuenta? Perderás todos tus datos y esta acción NO se puede deshacer.');">
                            <button type="submit" class="btn btn-danger">Eliminar mi cuenta</button>
                        </form>
                    </div>
                </section>

            </div>
        </main>
    </div>

</body>
</html>