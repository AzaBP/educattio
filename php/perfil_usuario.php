<?php
session_start();
include 'conexion.php';

// 1. SEGURIDAD: Si no está logueado, lo echamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// 2. OBTENER LOS DATOS ACTUALES DEL USUARIO
try {
    $sql = "SELECT nombre_completo, nombre_usuario, telefono, fecha_nacimiento, formacion_academica, experiencia_laboral, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id_usuario);
    $stmt->execute();
    
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
    <title>Educattio - Mi Perfil</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css"> 
    <link rel="stylesheet" href="../css/perfil_usuario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="dashboard-layout">
        
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="page-header">
                <h1>Mi Perfil Profesional</h1>
                <p>Completa tus datos para personalizar tu experiencia y currículum.</p>
            </header>

            <?php if(isset($_GET['exito'])): ?>
                <div style="background-color: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; max-width: 1000px; margin-left: auto; margin-right: auto;">
                    <i class="fas fa-check-circle"></i> ¡Perfil actualizado correctamente!
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; max-width: 1000px; margin-left: auto; margin-right: auto;">
                    <i class="fas fa-exclamation-circle"></i> Hubo un error al guardar los cambios. Inténtalo de nuevo.
                </div>
            <?php endif; ?>

            <form action="actualizar_perfil.php" method="POST" class="profile-grid">
                
                <section class="card personal-card">
                    <h3 class="card-title"><i class="fas fa-user"></i> Datos Personales</h3>
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($datos_usuario['nombre_completo']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label>Nombre de Usuario</label>
                            <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($datos_usuario['nombre_usuario']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Teléfono de Contacto</label>
                            <input type="tel" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono'] ?? ''); ?>" placeholder="+34 600 000 000">
                        </div>
                        <div class="input-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($datos_usuario['fecha_nacimiento'] ?? ''); ?>">
                        </div>
                    </div>
                </section>

                <section class="card academic-card">
                    <h3 class="card-title"><i class="fas fa-university"></i> Formación y Expediente</h3>
                    
                    <div class="input-group full-width">
                        <label>Formación Académica</label>
                        <textarea name="formacion_academica" rows="4" placeholder="Describe tus títulos, másters y cursos..."><?php echo htmlspecialchars($datos_usuario['formacion_academica'] ?? ''); ?></textarea>
                    </div>

                    <div class="input-group full-width">
                        <label>Expediente / Experiencia</label>
                        <textarea name="experiencia_laboral" rows="4" placeholder="Detalla tu experiencia laboral previa..."><?php echo htmlspecialchars($datos_usuario['experiencia_laboral'] ?? ''); ?></textarea>
                    </div>
                </section>

                <section class="card photo-card">
                    <h3 class="card-title"><i class="fas fa-camera"></i> Foto de Perfil</h3>
                    <div class="photo-container">
                        <?php 
                        $foto_actual = $datos_usuario['foto_perfil'] ?? '';
                        // Construir la ruta para el navegador desde /php/ hacia uploads/perfil
                        if (!empty($foto_actual)) {
                            $foto_url = '../' . ltrim($foto_actual, '/');
                        } else {
                            $foto_url = '../uploads/perfil/default-avatar.png';
                        }
                        ?>
                        <img src="<?php echo $foto_url; ?>" alt="Foto de perfil" class="profile-photo" id="previewFoto">
                        
                        <form action="subir_foto.php" method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                            <input type="file" name="foto_perfil" id="foto_perfil" accept="image/jpeg, image/png, image/webp">
                            <button type="submit" name="submit_foto">Subir foto</button>
                        </form>
                        
                        <?php if(isset($_GET['foto_ok'])): ?>
                            <div class="success-msg"><i class="fas fa-check-circle"></i> ¡Foto actualizada!</div>
                        <?php elseif(isset($_GET['foto_error'])): ?>
                            <div class="error-msg"><i class="fas fa-exclamation-triangle"></i> Error al subir la foto. Verifica formato y tamaño (max 2MB).</div>
                        <?php endif; ?>
                    </div>
                </section>

                <div class="form-actions-bar">
                    <a href="portal_inicio_usuario.php" class="btn btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; color: #666; border: 1px solid #ddd; background: white;">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>

            </form>
        </main>
    </div>

    <script>
        // Previsualización de imagen antes de subir
        document.getElementById('foto_perfil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>