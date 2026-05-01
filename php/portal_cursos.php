

<?php
require_once __DIR__ . '/controllers/auth_check.php';

// Obtener datos completos del usuario para el sidebar
$usuario_id = $_SESSION['usuario_id'];
try {
    require_once 'conexion.php';
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Mis Cursos</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css"> 
    <link rel="stylesheet" href="../css/portal_cursos.css"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>


    <div class="dashboard-layout">
        
        <?php include 'sidebar.php'; ?>

        <div class="sidebar-footer">
            <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>

        <main class="main-content">
            <header class="page-header-flex">
                <div>
                    <h1>Mis Cursos</h1>
                    <p>Gestiona tus destinos y años académicos</p>
                </div>
            </header>

            <div class="classes-grid">
            </div>
        </main>
    </div>



    <div id="modalCurso" class="modal-overlay">
        <div class="modal-window">
            
            <div class="modal-header">
                <h3>Crear nuevo curso</h3>
                <button class="close-btn" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formCrearCurso" class="modal-form">
                <input type="hidden" id="editCursoId" value="">
                
                <div class="form-group">
                    <label for="inputNombreCentro">Centro Educativo</label>
                    <input type="text" id="inputNombreCentro" placeholder="Ej. IES Cervantes" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="inputPoblacion">Población</label>
                        <input type="text" id="inputPoblacion" placeholder="Ej. Zaragoza" required>
                    </div>
                    <div class="form-group">
                        <label for="inputProvincia">Provincia</label>
                        <input type="text" id="inputProvincia" placeholder="Ej. Zaragoza" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="inputAnio">Año del Curso Lectivo</label>
                        <input type="text" id="inputAnio" value="2025-2026" required>
                    </div>
                    <div class="form-group">
                        <label>Color del Curso</label>
                        <div class="color-palette-container" style="display: flex; align-items: center; gap: 12px; margin-top: 8px;">
                            <div class="color-presets" id="colorPresets" style="display: flex; gap: 8px;">
                                <div class="color-dot" data-color="#ff7a59" onclick="selectPresetColor('#ff7a59', this)" style="background:#ff7a59;"></div>
                                <div class="color-dot" data-color="#4a90e2" onclick="selectPresetColor('#4a90e2', this)" style="background:#4a90e2;"></div>
                                <div class="color-dot" data-color="#47b39d" onclick="selectPresetColor('#47b39d', this)" style="background:#47b39d;"></div>
                                <div class="color-dot" data-color="#ffc107" onclick="selectPresetColor('#ffc107', this)" style="background:#ffc107;"></div>
                                <div class="color-dot" data-color="#9b59b6" onclick="selectPresetColor('#9b59b6', this)" style="background:#9b59b6;"></div>
                            </div>
                            <div style="height: 25px; width: 1px; background: #ddd;"></div>
                            <input type="color" id="inputColor" value="#ff7a59" oninput="deselectPresets()">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-save" id="btnGuardarCurso">Guardar</button>
                </div>
            </form>

        </div>
    </div>

    <script src="../js/portal_cursos.js"></script>
</body>
</html>