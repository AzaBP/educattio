<?php
// ==========================================================
// detalles_asignatura.php - Página dinámica de asignatura
// ==========================================================
require_once __DIR__ . '/controllers/auth_check.php';
require_once 'conexion.php';

// ----------------------------------------------------------
// 1. Obtener y validar el ID de la asignatura
// ----------------------------------------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: portal_inicio_usuario.php");
    exit();
}
$asignatura_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// ----------------------------------------------------------
// 2. Cargar datos de la asignatura, clase, curso y permisos
// ----------------------------------------------------------
try {
    // Verificar que la asignatura pertenece al usuario (a través de curso -> usuario_id)
    $sql = "SELECT a.*, c.nombre_clase, c.curso_id, cu.nombre_centro, cu.anio_academico, cu.poblacion, cu.provincia
            FROM asignaturas a
            JOIN clases c ON a.clase_id = c.id
            JOIN cursos cu ON c.curso_id = cu.id
            WHERE a.id = :asignatura_id AND cu.usuario_id = :usuario_id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':asignatura_id' => $asignatura_id, ':usuario_id' => $usuario_id]);
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$asignatura) {
        die("No tienes permiso para ver esta asignatura o no existe.");
    }
    
    $curso_id = $asignatura['curso_id'];
    $clase_id = $asignatura['clase_id'];
} catch (PDOException $e) {
    die("Error al cargar la asignatura: " . $e->getMessage());
}

// ----------------------------------------------------------
// 3. Manejo de acciones POST (añadir tema, periodos, editar/eliminar)
// ----------------------------------------------------------
// Auto-migración para añadir columna documento si no existe
try {
    $conexion->exec("ALTER TABLE temas_asignatura ADD COLUMN documento VARCHAR(255) NULL");
} catch (PDOException $e) {
    // Ignorar si la columna ya existe
}
// 3.1 Añadir tema
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_tema') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    if (!empty($titulo)) {
        // Obtener el último orden + 1
        $stmt = $conexion->prepare("SELECT MAX(orden) FROM temas_asignatura WHERE asignatura_id = ?");
        $stmt->execute([$asignatura_id]);
        $maxOrden = (int)$stmt->fetchColumn();
        $nuevoOrden = $maxOrden + 1;
        
        $documento_path = null;
        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/temarios/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = time() . '_' . basename($_FILES['documento']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['documento']['tmp_name'], $target_file)) {
                $documento_path = $filename;
            }
        }
        
        $stmt = $conexion->prepare("INSERT INTO temas_asignatura (asignatura_id, titulo, descripcion, orden, documento) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$asignatura_id, $titulo, $descripcion, $nuevoOrden, $documento_path]);
    }
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.2 Añadir periodos (varios separados por comas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_periodos') {
    $nombres = explode(',', $_POST['nombres'] ?? '');
    $nombres = array_map('trim', $nombres);
    $nombres = array_filter($nombres);
    if (!empty($nombres)) {
        // Eliminar periodos existentes (si quieres reemplazar, o evitar duplicados)
        // Opcional: borrar todos y volver a crear
        $stmtDel = $conexion->prepare("DELETE FROM periodos_evaluacion WHERE asignatura_id = ?");
        $stmtDel->execute([$asignatura_id]);
        
        $stmtIns = $conexion->prepare("INSERT INTO periodos_evaluacion (nombre_periodo, asignatura_id) VALUES (?, ?)");
        foreach ($nombres as $nombre) {
            if (!empty($nombre)) {
                $stmtIns->execute([$nombre, $asignatura_id]);
            }
        }
    }
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.3 Editar nombre de la asignatura (desde modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_asignatura') {
    $nuevoNombre = trim($_POST['nombre_asignatura'] ?? '');
    if (!empty($nuevoNombre)) {
        $stmt = $conexion->prepare("UPDATE asignaturas SET nombre_asignatura = ? WHERE id = ?");
        $stmt->execute([$nuevoNombre, $asignatura_id]);
    }
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.4 Eliminar tema individual
if (isset($_GET['delete_tema']) && is_numeric($_GET['delete_tema'])) {
    $temaId = (int)$_GET['delete_tema'];
    $stmt = $conexion->prepare("DELETE FROM temas_asignatura WHERE id = ? AND asignatura_id = ?");
    $stmt->execute([$temaId, $asignatura_id]);
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.5 Eliminar periodo individual
if (isset($_GET['delete_periodo']) && is_numeric($_GET['delete_periodo'])) {
    $pId = (int)$_GET['delete_periodo'];
    $stmt = $conexion->prepare("DELETE FROM periodos_evaluacion WHERE id = ? AND asignatura_id = ?");
    $stmt->execute([$pId, $asignatura_id]);
    header("Location: detalles_asignatura.php?id=" . $asignatura_id);
    exit();
}

// 3.6 Eliminar asignatura completa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_asignatura') {
    $stmt = $conexion->prepare("DELETE FROM asignaturas WHERE id = ?");
    $stmt->execute([$asignatura_id]);
    header("Location: detalles_curso.php?id=" . $curso_id);
    exit();
}

// ----------------------------------------------------------
// 4. Cargar datos para mostrar en la página
// ----------------------------------------------------------
// 4.1 Alumnos matriculados en esta asignatura
try {
    $sqlAlumnos = "SELECT a.id, a.nombre_alumno 
                   FROM alumnos a 
                   JOIN alumnos_asignaturas aa ON a.id = aa.alumno_id 
                   WHERE aa.asignatura_id = :asignatura_id
                   ORDER BY a.nombre_alumno";
    $stmtAlumnos = $conexion->prepare($sqlAlumnos);
    $stmtAlumnos->execute([':asignatura_id' => $asignatura_id]);
    $alumnos = $stmtAlumnos->fetchAll(PDO::FETCH_ASSOC);
    $totalAlumnos = count($alumnos);
} catch (PDOException $e) {
    $alumnos = [];
    $totalAlumnos = 0;
}

// 4.2 Periodos de evaluación
try {
    $sqlPeriodos = "SELECT * FROM periodos_evaluacion WHERE asignatura_id = :asignatura_id ORDER BY id";
    $stmtPeriodos = $conexion->prepare($sqlPeriodos);
    $stmtPeriodos->execute([':asignatura_id' => $asignatura_id]);
    $periodos = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);
    $totalPeriodos = count($periodos);
} catch (PDOException $e) {
    $periodos = [];
    $totalPeriodos = 0;
}

// 4.3 Eventos próximos (para la clase o asignatura? Usaremos eventos de la clase)
try {
    $sqlEventos = "SELECT * FROM eventos 
                   WHERE asignatura_id = :asignatura_id 
                   ORDER BY fecha ASC";
    $stmtEventos = $conexion->prepare($sqlEventos);
    $stmtEventos->execute([':asignatura_id' => $asignatura_id]);
    $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);
    $totalEventos = count($eventos);
} catch (PDOException $e) {
    $eventos = [];
    $totalEventos = 0;
}

// 4.4 Temario (temas)
try {
    $sqlTemas = "SELECT * FROM temas_asignatura WHERE asignatura_id = :asignatura_id ORDER BY orden ASC";
    $stmtTemas = $conexion->prepare($sqlTemas);
    $stmtTemas->execute([':asignatura_id' => $asignatura_id]);
    $temas = $stmtTemas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $temas = [];
}

// ----------------------------------------------------------
// 5. Obtener datos del usuario para el sidebar (avatar, etc.)
// ----------------------------------------------------------
try {
    $sqlUser = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmtUser = $conexion->prepare($sqlUser);
    $stmtUser->execute([':id' => $usuario_id]);
    $datos_usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($asignatura['nombre_asignatura']) ?> - Educattio</title>
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/global.css?v=2.1">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css?v=2.1">
    <link rel="stylesheet" href="../css/calendario.css?v=2.1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* ESTILOS DE ALTA PRIORIDAD PARA DETALLES ASIGNATURA */
        .dashboard-layout { display: flex; min-height: 100vh; background: #f8fafc; }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        
        .course-page-header-modern {
            position: relative;
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%) !important;
            padding: 3.5rem 2rem !important;
            border-radius: 24px !important;
            color: white !important;
            margin-bottom: 2.5rem !important;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15);
        }

        .header-main-content { display: flex; justify-content: space-between; align-items: flex-end; }
        .course-title-animate { font-size: 2.8rem; font-weight: 800; margin: 0.5rem 0; color: white; }
        
        .back-pill {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px;
            background: rgba(255,255,255,0.1); border-radius: 50px; color: white;
            text-decoration: none; font-size: 0.85rem; transition: 0.3s;
        }
        .back-pill:hover { background: rgba(255,255,255,0.2); color: white; transform: translateX(-5px); }

        .modern-badge {
            padding: 6px 14px; background: rgba(255,255,255,0.1); border-radius: 10px;
            font-size: 0.85rem; display: inline-flex; align-items: center; gap: 8px; margin-right: 10px;
        }

        .top-info-section { display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; margin-bottom: 3rem; }
        .info-card { background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; }
        .info-card h3 { font-size: 1.1rem; margin-bottom: 1.5rem; color: #1e293b; }
        .info-list { list-style: none; padding: 0; margin: 0; }
        .info-list li { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.2rem; }
        .icon-box { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .icon-box.blue { background: #eff6ff; color: #3b82f6; }
        .icon-box.green { background: #f0fdf4; color: #10b981; }
        .icon-box.red { background: #fef2f2; color: #ef4444; }

        .subject-detail-grid { display: grid; grid-template-columns: 1.3fr 1.5fr; gap: 2rem; }
        .box { background: white; padding: 1.5rem; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; }
        .box-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .box-header h2 { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin: 0; }

        .premium-card-wrapper { position: relative; margin-bottom: 1.5rem; }
        .card-options-container { position: absolute; top: 12px; right: 12px; z-index: 10; }
        .menu-dots-btn { 
            width: 34px; height: 34px; border-radius: 10px; background: rgba(255,255,255,0.9); 
            border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; cursor: pointer;
        }
        .dropdown-options-menu {
            position: absolute; top: 40px; right: 0; width: 170px; background: white; border-radius: 14px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: none; flex-direction: column; padding: 8px; z-index: 100;
        }
        .dropdown-options-menu.show { display: flex; }
        .dropdown-options-menu a { padding: 10px 14px; font-size: 0.9rem; color: #475569; display: flex; align-items: center; gap: 10px; border-radius: 10px; }
        .dropdown-options-menu a:hover { background: #f1f5f9; color: #2563eb; }
        
        .section-divider { border: 0; height: 1px; background: #e2e8f0; margin: 3rem 0; }
        .hidden { display: none !important; }
    </style>

    <script>
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

        function toggleSection(id) {
            const el = document.getElementById(id);
            if (el) el.classList.toggle('hidden');
        }

        async function eliminarTema(id) {
            if (!confirm('¿Seguro que quieres eliminar este tema?')) return;
            window.location.href = `detalles_asignatura.php?id=<?= $asignatura_id ?>&delete_tema=${id}`;
        }
        
        async function eliminarPeriodo(id) {
            if (!confirm('¿Seguro que quieres eliminar este periodo?')) return;
            window.location.href = `detalles_asignatura.php?id=<?= $asignatura_id ?>&delete_periodo=${id}`;
        }

        function editarTema(id) {
            alert('Función de edición de tema próximamente disponible.');
        }

        function openSettingsModal() { document.getElementById('settingsModal').style.display = 'flex'; }
        function closeSettingsModal() { document.getElementById('settingsModal').style.display = 'none'; }
    </script>
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <main class="main-content subject-detail-page">
        <!-- CABECERA PREMIUM -->
        <header class="course-page-header-modern">
            <div class="header-glass-overlay"></div>
            <div class="header-main-content">
                <div class="header-left">
                    <a href="detalles_clase.php?id=<?= $clase_id ?>" class="back-pill">
                        <i class="fas fa-chevron-left"></i> Volver a <?= htmlspecialchars($asignatura['nombre_clase']) ?>
                    </a>
                    <h1 class="course-title-animate"><?= htmlspecialchars($asignatura['nombre_asignatura']) ?></h1>
                    <div class="header-badges-row">
                        <span class="modern-badge"><i class="fas fa-university"></i> <?= htmlspecialchars($asignatura['nombre_centro']) ?></span>
                        <span class="modern-badge"><i class="fas fa-users"></i> <?= htmlspecialchars($asignatura['nombre_clase']) ?></span>
                        <span class="modern-badge"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($asignatura['anio_academico']) ?></span>
                    </div>
                </div>
                <div class="header-right">
                    <button class="settings-glass-btn" onclick="openSettingsModal()">
                        <i class="fas fa-sliders-h"></i> Ajustes de Asignatura
                    </button>
                </div>
            </div>
        </header>

        <section class="top-info-section">
            <div class="info-card">
                <h3>Resumen de Asignatura</h3>
                <ul class="info-list">
                    <li>
                        <div class="icon-box blue"><i class="fas fa-users"></i></div>
                        <div>
                            <strong><?= $totalAlumnos ?> Alumnos</strong>
                            <span>Participando en esta materia</span>
                        </div>
                    </li>
                    <li>
                        <div class="icon-box green"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <strong><?= $totalPeriodos ?> Periodos</strong>
                            <span>Etapas de evaluación</span>
                        </div>
                    </li>
                    <li>
                        <div class="icon-box red"><i class="fas fa-bell"></i></div>
                        <div>
                            <strong><?= $totalEventos ?> Eventos</strong>
                            <span>Pruebas y fechas clave</span>
                        </div>
                    </li>
                </ul>
            </div>
            
            <div class="overview-card evaluation-top-card" style="flex: 1.5; background: white; padding: 1.5rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; color:#1e293b;">Evaluaciones & Periodos</h3>
                        <p class="text-muted small m-0">Gestiona los trimestres y accede al cuaderno.</p>
                    </div>
                    <a id="abrirCuadernoBtn" class="btn btn-primary btn-sm" href="cuaderno_evaluacion.php?asig_id=<?= $asignatura_id ?>&periodo_id=<?= !empty($periodos) ? $periodos[0]['id'] : '' ?>" <?= empty($periodos) ? 'onclick="alert(\'Crea un periodo primero\'); return false;"' : '' ?>>
                        <i class="fas fa-book-open"></i> Cuaderno
                    </a>
                </div>
                
                <div id="periodosList" class="list-group list-group-flush" style="max-height: 180px; overflow-y: auto;">
                    <?php if (empty($periodos)): ?>
                        <div class="empty-state text-center py-3">No hay periodos definidos.</div>
                    <?php else: ?>
                        <?php foreach ($periodos as $periodo): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-calendar-alt text-primary" style="font-size: 0.9rem;"></i>
                                    <span style="font-size: 0.95rem; font-weight: 500;"><?= htmlspecialchars($periodo['nombre_periodo']) ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="cuaderno_evaluacion.php?asig_id=<?= $asignatura_id ?>&periodo_id=<?= $periodo['id'] ?>" class="btn btn-sm btn-light" title="Ver cuaderno"><i class="fas fa-eye"></i></a>
                                    <button class="btn btn-sm btn-light text-danger" onclick="eliminarPeriodo(<?= $periodo['id'] ?>)" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="mt-3 text-end">
                    <button class="btn btn-sm btn-outline-primary" onclick="toggleSection('nuevoPeriodoSection')">
                        <i class="fas fa-plus"></i> Añadir periodo
                    </button>
                </div>

                <div id="nuevoPeriodoSection" class="new-item-card hidden mt-3" style="background: #f8fafc; padding: 1rem; border-radius: 12px;">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_periodos">
                        <div class="mb-2">
                            <input type="text" name="nombres" class="form-control form-control-sm" placeholder="Ej: 1º Trimestre" required>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSection('nuevoPeriodoSection')">Cancelar</button>
                            <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <hr class="section-divider">

        <section class="subject-detail-grid">
            <!-- Bloque de Eventos -->
            <div class="box box-events">
                <div class="box-header">
                    <div>
                        <h2>Calendario & Eventos</h2>
                        <p>Eventos próximos para esta clase</p>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="abrirMiniEventoAsignatura()">
                        <i class="fas fa-plus"></i> Añadir evento
                    </button>
                </div>
                <!-- Mini-calendario para crear eventos -->
                <div
                    id="miniCalendarAsignaturaContainer"
                    class="mini-calendar-container mb-4"
                    data-asignatura-id="<?= (int)$asignatura_id ?>"
                    data-clase-id="<?= (int)$clase_id ?>"
                ></div>
                <div id="eventosList" class="events-list">
                    <?php if (empty($eventos)): ?>
                        <div class="empty-state" id="eventosEmpty">No hay eventos programados para esta asignatura.</div>
                    <?php else: ?>
                        <?php foreach ($eventos as $evento): ?>
                            <div class="mini-event-item">
                                <div>
                                    <div class="mini-event-title"><?= htmlspecialchars($evento['titulo']) ?></div>
                                    <small style="color:#6b7280;"><?= date('d/m/Y H:i', strtotime($evento['fecha'])) ?></small>
                                    <p style="margin:4px 0 0; font-size:0.8rem; color:#4b5563;"><?= htmlspecialchars($evento['descripcion'] ?? '') ?></p>
                                </div>
                                <span class="mini-event-type <?= strtolower($evento['tipo_evento'] ?? '') ?>"><?= htmlspecialchars($evento['tipo_evento'] ?? '') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bloque Temario -->
            <div class="box box-syllabus">
                <div class="box-header">
                    <div>
                        <h2>Temario</h2>
                        <p>Organiza los contenidos y objetivos de la asignatura.</p>
                    </div>
                    <button class="btn btn-outline-primary" onclick="toggleSection('nuevoTemaSection')">
                        <i class="fas fa-plus"></i> Añadir tema
                    </button>
                </div>
                <div id="temasContainer" class="temas-list">
                    <?php if (empty($temas)): ?>
                        <div class="empty-state" id="temasEmpty">Añade los primeros temas para compartir el temario.</div>
                    <?php else: ?>
                        <?php foreach ($temas as $tema): ?>
                            <div class="premium-card-wrapper">
                                <div class="card-options-container">
                                    <button class="menu-dots-btn" onclick="toggleMenu(event, 'tema-<?= $tema['id'] ?>')">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="dropdown-tema-<?= $tema['id'] ?>" class="dropdown-options-menu">
                                        <a href="javascript:void(0)" onclick="editarTema(<?= $tema['id'] ?>)"><i class="fas fa-edit"></i> Modificar</a>
                                        <a href="javascript:void(0)" onclick="eliminarTema(<?= $tema['id'] ?>)" class="delete-option"><i class="fas fa-trash"></i> Eliminar</a>
                                    </div>
                                </div>
                                <div class="premium-card" style="--accent-color: #64748b; margin-bottom: 1.5rem;">
                                    <div class="card-banner" style="height: 40px;">
                                        <div class="card-badge">Tema <?= htmlspecialchars($tema['orden']) ?></div>
                                    </div>
                                    <div class="card-content">
                                        <h3><?= htmlspecialchars($tema['titulo']) ?></h3>
                                        <p><?= nl2br(htmlspecialchars($tema['descripcion'])) ?></p>
                                        
                                        <?php if (!empty($tema['documento'])): ?>
                                            <div class="card-footer">
                                                <a href="../uploads/temarios/<?= htmlspecialchars($tema['documento']) ?>" target="_blank" style="color: var(--accent-color); text-decoration: none;">
                                                    <i class="fas fa-paperclip"></i> Ver documento
                                                </a>
                                                <i class="fas fa-file-download"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div id="nuevoTemaSection" class="new-item-card hidden">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_tema">
                        <div class="mb-3">
                            <label class="form-label">Título del tema</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ej: Ecosistemas" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe el contenido del tema"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento adjunto (Opcional)</label>
                            <input type="file" name="documento" class="form-control">
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleSection('nuevoTemaSection')">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar tema</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Modal de Ajustes (editar nombre / eliminar) -->
<div id="settingsModal" class="modal-overlay" style="display: none;">
    <div class="modal-window">
        <div class="modal-header">
            <h3>Ajustes de la asignatura</h3>
            <button class="close-btn" onclick="closeSettingsModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit_asignatura">
            <div class="form-group mb-3">
                <label class="form-label">Nombre de la asignatura</label>
                <input type="text" name="nombre_asignatura" class="form-control" value="<?= htmlspecialchars($asignatura['nombre_asignatura']) ?>" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="closeSettingsModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
        <hr>
        <div class="danger-zone-modal">
            <div class="danger-info">
                <h4><i class="fas fa-exclamation-triangle"></i> Zona peligrosa</h4>
                <p>Esta acción eliminará la asignatura, todos sus temas, periodos, items de evaluación y notas asociadas. No se puede deshacer.</p>
            </div>
            <form method="POST" onsubmit="return confirm('¿Estás completamente seguro? Se perderán todos los datos de esta asignatura.');">
                <input type="hidden" name="action" value="delete_asignatura">
                <button type="submit" class="btn-delete-course">
                    <i class="fas fa-trash-alt"></i> Eliminar asignatura
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Funciones simples para mostrar/ocultar secciones y modal
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
    } else {
        section.classList.add('hidden');
    }
}

function openSettingsModal() {
    document.getElementById('settingsModal').style.display = 'flex';
}
function closeSettingsModal() {
    document.getElementById('settingsModal').style.display = 'none';
}
// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('settingsModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
.hidden { display: none; }
.event-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    border-bottom: 1px solid #eee;
}
.event-date {
    text-align: center;
    min-width: 55px;
}
.event-day {
    font-size: 1.5rem;
    font-weight: bold;
    display: block;
}
.event-month {
    text-transform: uppercase;
    font-size: 0.7rem;
}
.tema-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 12px;
}
.tema-card h4 {
    margin-bottom: 8px;
}
.new-item-card {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 16px;
}
.modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    justify-content: center;
    align-items: center;
    z-index: 2000;
}
.modal-window {
    background: white;
    border-radius: 24px;
    max-width: 550px;
    width: 90%;
    padding: 24px;
    box-shadow: 0 20px 35px rgba(0,0,0,0.2);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.close-btn {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
}
.danger-zone-modal {
    margin-top: 24px;
    padding: 16px;
    background: #fff5f5;
    border-radius: 16px;
    border: 1px solid #ffcccc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}
.btn-delete-course {
    background: white;
    border: 1px solid #d32f2f;
    color: #d32f2f;
    padding: 8px 20px;
    border-radius: 30px;
    font-weight: 600;
    transition: 0.2s;
}
.btn-delete-course:hover {
    background: #d32f2f;
    color: white;
}
@media (max-width: 700px) {
    .danger-zone-modal { flex-direction: column; align-items: flex-start; }
    .btn-delete-course { width: 100%; text-align: center; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/calendar-sync.js?v=2.0"></script>
<script src="../js/mini-calendar.js?v=2.0"></script>
<script src="../js/detalles_asignatura.js?v=2.0"></script>
</body>
</html>