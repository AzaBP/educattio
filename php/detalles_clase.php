<div id="modalEditarAlumno" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Editar Alumno</h3>
            <button class="close-btn" onclick="cerrarModalEditarAlumno()"><i class="fas fa-times"></i></button>
        </div>
        <form id="formEditarAlumno" onsubmit="guardarEdicionAlumno(event)">
            <input type="hidden" id="editAlumnoId">
            <div class="form-group mb-3">
                <label>Nombre Completo</label>
                <input type="text" id="editNombreAlumno" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label>Observaciones</label>
                <textarea id="editObsAlumno" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group mb-3 text-center p-3 rounded bg-light border">
                <label class="mb-2 fw-bold text-muted">Avatar</label>
                <input type="hidden" id="editFotoAlumno" value="">
                <div class="d-flex justify-content-center flex-wrap gap-2" id="lista-iconos-editar"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEditarAlumno()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
<?php
require_once __DIR__ . '/controllers/auth_check.php';
require_once __DIR__ . '/conexion.php';

$clase_id = $_GET['id'] ?? null;

if (!$clase_id) {
    die("Error: No se ha especificado ninguna clase.");
}

// OBTENER DATOS BÁSICOS DE LA CLASE (Para el título y el botón de volver)
$sql = "SELECT c.nombre_clase, c.curso_id, cur.nombre_centro 
        FROM clases c 
        JOIN cursos cur ON c.curso_id = cur.id 
        WHERE c.id = :clase_id";
$stmt = $conexion->prepare($sql);
$stmt->execute([':clase_id' => $clase_id]);
$datos_clase = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos_clase) {
    die("Error: La clase no existe.");
}

// Variables para el sidebar
$nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario';
$fotoUsuario = isset($_SESSION['foto']) ? $_SESSION['foto'] : '../imagenes/icons8-profesor-100.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Detalles de la Clase</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link rel="stylesheet" href="../css/detalles_curso.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Estilos específicos para las pestañas de esta vista */
        .nav-tabs .nav-link { color: #6b7280; font-weight: 600; border: none; padding: 15px 25px; }
        .nav-tabs .nav-link.active { color: #3b82f6; border-bottom: 3px solid #3b82f6; background: transparent; }
        .tab-content { padding: 30px 0; }
        .btn-add-element { border: 2px dashed #cbd5e1; background: transparent; color: #64748b; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.2s; }
        .btn-add-element:hover { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header class="course-page-header">
                <div class="header-top-row">
                    <a href="detalles_curso.php?id=<?php echo $datos_clase['curso_id']; ?>" class="back-link">
                        <i class="fas fa-arrow-left"></i> Volver a <?php echo htmlspecialchars($datos_clase['nombre_centro']); ?>
                    </a>
                </div>
                <div class="header-content mt-3">
                    <h1><?php echo htmlspecialchars($datos_clase['nombre_clase']); ?></h1>
                    <p class="text-muted">Gestión de asignaturas y alumnado</p>
                </div>
            </header>

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="asignaturas-tab" data-bs-toggle="tab" data-bs-target="#asignaturas" type="button" role="tab"><i class="fas fa-book"></i> Asignaturas</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="alumnos-tab" data-bs-toggle="tab" data-bs-target="#alumnos" type="button" role="tab"><i class="fas fa-users"></i> Alumnos matriculados</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="asignaturas" role="tabpanel">
                    <div class="row" id="contenedor-asignaturas">
                        <div class="col-md-4 mb-4">
                            <div class="btn-add-element h-100 d-flex flex-column justify-content-center align-items-center" onclick="abrirModalNuevaAsignatura()">
                                <i class="fas fa-plus mb-2" style="font-size: 1.5rem;"></i>
                                <span>Añadir Nueva Asignatura</span>
                            </div>
                        </div>
                        </div>
                </div>

                <div class="tab-pane fade" id="alumnos" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="m-0" style="font-size: 1.2rem; font-family: 'Georgia', serif;">Listado de la clase</h3>
                        <button class="btn btn-primary" onclick="abrirModalNuevoAlumno()" style="border-radius: 8px;"><i class="fas fa-user-plus"></i> Añadir Alumno</button>
                    </div>
                    
                    <div class="table-responsive bg-white rounded-3 shadow-sm p-3">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Nombre del Alumno</th>
                                    <th>Observaciones</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpo-tabla-alumnos">
                                <tr><td colspan="3" class="text-center text-muted py-4">Cargando alumnos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Guardamos el ID de la clase para usarlo en nuestras funciones AJAX futuras
        const CLASE_ACTUAL_ID = <?php echo $clase_id; ?>;
    </script>

<div id="modalAsignatura" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Nueva Asignatura</h3>
            <button class="close-btn" onclick="cerrarModalAsignatura()"><i class="fas fa-times"></i></button>
        </div>
        <form id="formAsignatura" onsubmit="guardarAsignatura(event)">
            <div class="form-group mb-3">
                <label>Nombre de la Asignatura</label>
                <input type="text" id="nombreAsignatura" class="form-control" placeholder="Ej: Matemáticas II" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalAsignatura()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Asignatura</button>
            </div>
        </form>
    </div>
</div>

<div id="modalAlumno" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Añadir Alumno</h3>
            <button class="close-btn" onclick="cerrarModalAlumno()"><i class="fas fa-times"></i></button>
        </div>
        <form id="formAlumno" onsubmit="guardarAlumno(event)">
            <div class="form-group mb-3">
                <label>Nombre Completo</label>
                <input type="text" id="nombreAlumno" class="form-control" placeholder="Apellidos, Nombre" required>
            </div>
            <div class="form-group mb-3">
                <label>Observaciones iniciales (opcional)</label>
                <textarea id="obsAlumno" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group mb-3 text-center p-3 rounded bg-light border">
                <label class="mb-2 fw-bold text-muted">Avatar (Opcional)</label>
                <input type="hidden" id="fotoNuevoAlumno" value="">
                <div class="d-flex justify-content-center flex-wrap gap-2" id="lista-iconos-nuevo">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalAlumno()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Alumno</button>
            </div>
        </form>
    </div>
</div>
<div id="modalGestion" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="tituloModalGestion">Gestionar Matrícula</h3>
            <button class="close-btn" onclick="cerrarModalGestion()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p id="subtituloModal" class="text-muted small mb-3"></p>
            <div id="lista-checks-gestion" class="list-group" style="max-height: 400px; overflow-y: auto;">
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalGestion()">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarGestion">Guardar Cambios</button>
        </div>
    </div>
</div>

<script src="../js/detalles_clase.js"></script>
</body>
</html>