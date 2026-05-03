
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

require_once __DIR__ . '/conexion.php';

$clase_id = $_GET['id'] ?? null;

if (!$clase_id) {
    die("Error: No se ha especificado ninguna clase.");
}

// OBTENER DATOS BÁSICOS DE LA CLASE (Para el título y el botón de volver)
$sql = "SELECT c.nombre_clase, c.curso_id, cur.nombre_centro, cur.anio_academico 
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
    <link rel="stylesheet" href="../css/global.css?v=2.2">
    <link rel="stylesheet" href="../css/detalles_clase.css?v=2.2">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css?v=2.2">
    <link rel="stylesheet" href="../css/calendario.css?v=2.2">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* REFUERZO DE ESTILOS PREMIUM PARA ESTA VISTA */
        .course-page-header-modern {
            position: relative;
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%) !important;
            padding: 3.5rem 2rem !important;
            border-radius: 24px !important;
            color: white !important;
            margin-bottom: 2.5rem !important;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15);
            overflow: hidden;
        }

        .header-glass-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.1), transparent);
            pointer-events: none;
        }

        .header-main-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .course-title-animate {
            font-size: 2.8rem;
            font-weight: 800;
            margin: 0.5rem 0;
            letter-spacing: -0.02em;
        }

        .back-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            color: white !important;
            text-decoration: none !important;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            transition: 0.3s;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .back-pill:hover { background: rgba(255,255,255,0.2); transform: translateX(-5px); }

        .header-badges-row { display: flex; gap: 12px; }
        .modern-badge {
            padding: 6px 14px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Estilos específicos para las pestañas de esta vista */
        .nav-tabs { border-bottom: 1px solid #e2e8f0; margin-bottom: 2rem; }
        .nav-tabs .nav-link { color: #64748b; font-weight: 600; border: none; padding: 12px 24px; transition: 0.3s; }
        .nav-tabs .nav-link.active { color: #2563eb; border-bottom: 3px solid #2563eb; background: transparent; }
        .nav-tabs .nav-link:hover:not(.active) { color: #1e293b; background: #f1f5f9; border-radius: 10px 10px 0 0; }
        
        .tab-content { padding: 0; }
        .btn-add-element { border: 2px dashed #e2e8f0; background: white; color: #64748b; border-radius: 20px; padding: 2.5rem; text-align: center; cursor: pointer; transition: 0.3s; width: 100%; }
        .btn-add-element:hover { border-color: #2563eb; color: #2563eb; background: #eff6ff; transform: translateY(-3px); }
        
        /* DOTS MENU */
        .premium-card-wrapper { position: relative; }
        .card-options-container { position: absolute; top: 12px; right: 12px; z-index: 10; }
        .menu-dots-btn { width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.9); border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .dropdown-options-menu {
            position: absolute; top: 40px; right: 0; width: 170px; background: white; border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: none; flex-direction: column; padding: 8px; z-index: 100;
        }
        .dropdown-options-menu.show { display: flex; }
        .dropdown-options-menu a { padding: 10px 14px; font-size: 0.9rem; color: #475569; display: flex; align-items: center; gap: 10px; border-radius: 10px; text-decoration: none; }
        .dropdown-options-menu a:hover { background: #f1f5f9; color: #2563eb; }
        /* PREMIUM MODAL STYLING */
        .modal-content {
            border-radius: 28px !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 80px rgba(0,0,0,0.15) !important;
            overflow: hidden;
        }

        .modal-header {
            padding: 2rem 2rem 1rem !important;
            border-bottom: none !important;
        }

        .modal-title {
            font-size: 1.6rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em;
        }

        .modal-body {
            padding: 1.5rem 2rem !important;
        }

        .modal-footer {
            padding: 1.5rem 2rem 2rem !important;
            border-top: none !important;
        }

        .modal-input-wrapper {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 4px 16px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal-input-wrapper:focus-within {
            background: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            transform: translateY(-1px);
        }

        .modal-input-wrapper i {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .modal-input-wrapper .form-control {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 12px 0 !important;
            font-weight: 500;
            color: #1e293b;
        }

        .modal-label {
            font-weight: 700;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: block;
            margin-left: 4px;
        }

        .btn-modal-primary {
            background: #0f172a !important;
            color: white !important;
            border: none !important;
            padding: 12px 24px !important;
            border-radius: 14px !important;
            font-weight: 600 !important;
            transition: 0.3s !important;
        }

        .btn-modal-primary:hover {
            background: #1e293b !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
        }

        .btn-modal-secondary {
            background: #f1f5f9 !important;
            color: #475569 !important;
            border: none !important;
            padding: 12px 24px !important;
            border-radius: 14px !important;
            font-weight: 600 !important;
            transition: 0.3s !important;
        }

        .btn-modal-secondary:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
        }

        .avatar-option {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: 0.2s;
            padding: 2px;
        }

        .avatar-option:hover { transform: scale(1.1); }
        .avatar-option.selected {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.1);
            transform: scale(1.15);
        }

        /* COLOR AND ICON PICKER */
        .color-circles, .icon-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            padding: 15px;
            background: rgba(15, 23, 42, 0.02);
            border-radius: 20px;
        }

        .color-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: 0.3s;
        }

        .color-circle:hover { transform: scale(1.2); }
        .color-circle.selected {
            border-color: #0f172a;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .icon-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid #e2e8f0;
            color: #64748b;
            cursor: pointer;
            transition: 0.3s;
            font-size: 1.1rem;
        }

        .icon-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }

        .icon-btn.selected {
            background: #0f172a;
            color: white;
            border-color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(15, 23, 42, 0.2);
        }

        /* Backdrop blur */
        .modal-backdrop.show {
            backdrop-filter: blur(8px);
            background-color: rgba(15, 23, 42, 0.4);
        }
        .premium-list-group .list-group-item {
            border: none;
            border-bottom: 1px solid #f1f5f9;
            padding: 14px 20px;
            transition: 0.2s;
            cursor: pointer;
        }

        .premium-list-group .list-group-item:hover {
            background-color: #f8fafc;
            padding-left: 25px;
        }

        .premium-list-group .list-group-item:last-child { border-bottom: none; }

        .premium-list-group .form-check-input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <!-- CABECERA PREMIUM -->
            <header class="course-page-header-modern">
                <div class="header-glass-overlay"></div>
                <div class="header-main-content">
                    <div class="header-left">
                        <a href="detalles_curso.php?id=<?php echo $datos_clase['curso_id']; ?>" class="back-pill">
                            <i class="fas fa-chevron-left"></i> Volver a <?php echo htmlspecialchars($datos_clase['nombre_centro']); ?>
                        </a>
                        <h1 class="course-title-animate"><?php echo htmlspecialchars($datos_clase['nombre_clase']); ?></h1>
                        <div class="header-badges-row">
                            <span class="modern-badge"><i class="fas fa-university"></i> <?php echo htmlspecialchars($datos_clase['nombre_centro']); ?></span>
                            <span class="modern-badge"><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($datos_clase['anio_academico']); ?></span>
                        </div>
                    </div>
                    <div class="header-right">
                        <!-- Aquí se podrían añadir acciones rápidas de la clase -->
                    </div>
                </div>
            </header>

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="calendario-tab" data-bs-toggle="tab" data-bs-target="#calendario" type="button" role="tab"><i class="fas fa-calendar-alt"></i> Calendario</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="asignaturas-tab" data-bs-toggle="tab" data-bs-target="#asignaturas" type="button" role="tab"><i class="fas fa-book"></i> Asignaturas</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="alumnos-tab" data-bs-toggle="tab" data-bs-target="#alumnos" type="button" role="tab"><i class="fas fa-users"></i> Alumnos matriculados</button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="calendario" role="tabpanel">
                    <div class="d-flex justify-content-center align-items-center mb-4 mt-3">
                        <h3 class="m-0" style="font-size: 1.2rem; font-family: 'Georgia', serif;">Eventos de la clase</h3>
                    </div>
                    <div class="d-flex justify-content-center w-100">
                        <div id="miniCalendarClaseContainer" style="padding: 1rem 0; width: 100%; max-width: 900px;"></div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="asignaturas" role="tabpanel">
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
                        <div class="d-flex align-items-center gap-3">
                            <h3 class="m-0" style="font-size: 1.2rem; font-family: 'Georgia', serif;">Listado de la clase</h3>
                            <button class="btn btn-outline-secondary btn-sm" onclick="exportarAlumnosPDF()" style="border-radius: 8px;">
                                <i class="fas fa-file-pdf"></i> Exportar Lista
                            </button>
                        </div>
                        <button class="btn btn-primary" onclick="abrirModalNuevoAlumno()" style="border-radius: 8px;"><i class="fas fa-user-plus"></i> Añadir Alumno</button>
                    </div>
                    
                    <div class="table-responsive bg-white rounded-3 shadow-sm p-3">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Alumno</th>
                                    <th>Contacto</th>
                                    <th>Salud / Alergias</th>
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
        
        function toggleMenu(event, id) {
            event.preventDefault();
            event.stopPropagation();
            document.querySelectorAll('.dropdown-options-menu').forEach(m => {
                if (m.id !== `dropdown-${id}`) m.classList.remove('show');
            });
            const menu = document.getElementById(`dropdown-${id}`);
            if (menu) menu.classList.toggle('show');
        }
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-options-menu').forEach(m => m.classList.remove('show'));
        });
    </script>

<!-- MODAL NUEVA ASIGNATURA -->
<div class="modal fade" id="modalNuevaAsignatura" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Nueva Asignatura</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevaAsignatura">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="modal-label">Nombre de la Asignatura</label>
                        <div class="modal-input-wrapper">
                            <i class="fas fa-book"></i>
                            <input type="text" id="nuevoNombreAsignatura" class="form-control" placeholder="Ej: Matemáticas II" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="modal-label">Color de la Asignatura</label>
                        <input type="hidden" id="nuevoColorAsig" value="#4facfe">
                        <div class="color-circles" id="nuevo-color-asig-container"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="modal-label">Icono</label>
                        <input type="hidden" id="nuevoIconoAsig" value="fa-book">
                        <div class="icon-grid" id="nuevo-icono-asig-container"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-modal-primary">Crear Asignatura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDITAR ASIGNATURA -->
<div class="modal fade" id="modalEditarAsignatura" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modificar Asignatura</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarAsignatura" onsubmit="guardarAsignatura(event, true)">
                <input type="hidden" id="editAsigId" value="">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="modal-label">Nombre de la Asignatura</label>
                        <div class="modal-input-wrapper">
                            <i class="fas fa-edit"></i>
                            <input type="text" id="editNombreAsignatura" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="modal-label">Color Personalizado</label>
                        <input type="hidden" id="editColorAsig" value="">
                        <div class="color-circles" id="edit-color-asig-container"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="modal-label">Icono</label>
                        <input type="hidden" id="editIconoAsig" value="">
                        <div class="icon-grid" id="edit-icono-asig-container"></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger px-4" style="border-radius:14px; font-weight:600;" onclick="eliminarAsignaturaModal()">Eliminar</button>
                    <div>
                        <button type="button" class="btn-modal-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-modal-primary">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL NUEVO ALUMNO -->
<div class="modal fade" id="modalAlumno" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Añadir Alumno</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAlumno">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="modal-label">Nombre Completo</label>
                        <div class="modal-input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nombreAlumno" class="form-control" placeholder="Apellidos, Nombre" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="modal-label">Teléfono de contacto</label>
                            <div class="modal-input-wrapper">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="telefonoAlumno" class="form-control" placeholder="+34 600 000 000">
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="modal-label">Persona de contacto</label>
                            <div class="modal-input-wrapper">
                                <i class="fas fa-user-friends"></i>
                                <input type="text" id="contactoAlumno" class="form-control" placeholder="Ej: Madre / Tutor">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="modal-label">Salud / Alergias</label>
                        <textarea id="alergiasAlumno" class="form-control" rows="2" placeholder="Ej: Asma, intolerancia a la lactosa..." style="border-radius:16px; border:1px solid #e2e8f0; padding:12px; background:#f8fafc; transition:0.3s;"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="modal-label">Observaciones iniciales</label>
                        <textarea id="obsAlumno" class="form-control" rows="2" style="border-radius:16px; border:1px solid #e2e8f0; padding:12px; background:#f8fafc; transition:0.3s;"></textarea>
                    </div>
                    
                    <div class="mb-2 p-3 text-center" style="background: rgba(15, 23, 42, 0.02); border-radius: 20px;">
                        <label class="modal-label mb-3">Selecciona un Avatar</label>
                        <input type="hidden" id="fotoNuevoAlumno" value="">
                        <div class="d-flex justify-content-center flex-wrap gap-3" id="lista-iconos-nuevo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-modal-primary">Registrar Alumno</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDITAR ALUMNO -->
<div class="modal fade" id="modalEditarAlumno" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="tituloFichaAlumno">Ficha del Alumno</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarAlumno">
                <input type="hidden" id="editAlumnoId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="modal-label">Nombre Completo</label>
                        <div class="modal-input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="editNombreAlumno" class="form-control" readonly required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="modal-label">Teléfono de contacto</label>
                            <div class="modal-input-wrapper">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="editTelefonoAlumno" class="form-control" placeholder="+34 600 000 000" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="modal-label">Persona de contacto</label>
                            <div class="modal-input-wrapper">
                                <i class="fas fa-user-friends"></i>
                                <input type="text" id="editContactoAlumno" class="form-control" placeholder="Ej: Madre / Tutor" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="modal-label">Salud / Alergias</label>
                        <textarea id="editAlergiasAlumno" class="form-control" rows="2" style="border-radius:16px; border:1px solid #e2e8f0; padding:12px; background:#f8fafc; transition:0.3s;" readonly></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="modal-label">Observaciones</label>
                        <textarea id="editObsAlumno" class="form-control" rows="2" style="border-radius:16px; border:1px solid #e2e8f0; padding:12px; background:#f8fafc; transition:0.3s;" readonly></textarea>
                    </div>
                    
                    <div class="mb-2 p-3 text-center" id="contenedorIconosEditar" style="background: rgba(15, 23, 42, 0.02); border-radius: 20px; display:none;">
                        <label class="modal-label mb-3">Actualizar Avatar</label>
                        <input type="hidden" id="editFotoAlumno" value="">
                        <div class="d-flex justify-content-center flex-wrap gap-3" id="lista-iconos-editar">
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger px-4" id="btnEliminarAlumno" style="border-radius:14px; font-weight:600; display:none;" onclick="eliminarAlumnoModal()">Eliminar</button>
                    <div id="footerAccionesVista">
                        <button type="button" class="btn-modal-secondary me-2" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn-modal-primary" id="btnActivarEdicion" onclick="activarModoEdicion()">Modificar Datos</button>
                    </div>
                    <div id="footerAccionesEdicion" style="display:none;">
                        <button type="button" class="btn-modal-secondary me-2" onclick="desactivarModoEdicion()">Cancelar</button>
                        <button type="submit" class="btn-modal-primary">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL GESTIONAR MATRICULA -->
<div class="modal fade" id="modalGestion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="tituloModalGestion" class="modal-title">Gestionar Matrícula</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="subtituloModal" class="text-muted small mb-4"></p>
                <div id="lista-checks-gestion" class="list-group premium-list-group" style="max-height: 400px; overflow-y: auto; border-radius: 16px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-modal-primary" id="btnGuardarGestion">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
<script src="../js/calendar-sync.js?v=1.3"></script>
<script src="../js/mini-calendar.js?v=1.3"></script>
<script src="../js/detalles_clase.js?v=1.3"></script>
</body>
</html>