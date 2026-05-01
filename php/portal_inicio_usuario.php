<?php
// 1. INICIO DEL "CEREBRO" (PHP)
session_start();

require_once 'conexion.php';  


// Seguridad: Si no hay un nombre de usuario en la sesión, el sistema te expulsa al login
if (!isset($_SESSION['nombre_usuario'])) {
    header("Location: login.php");
    exit();
}

// Guardamos el nombre en una variable para usarlo abajo
$nombre_usuario = $_SESSION['nombre_usuario'];

// Guardamos el nombre en una variable para usarlo abajo
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos completos del usuario para el sidebar
try {
    $sql = "SELECT nombre_completo, nombre_usuario, foto_perfil FROM usuarios WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $usuario_id]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $datos_usuario = null;
}

// Calcular año académico actual y contar cursos activos (igual que antes)
$anio_actual = date('Y');
$mes_actual = date('n');
if ($mes_actual >= 9) {
    $anio_academico = $anio_actual . '-' . ($anio_actual + 1);
} else {
    $anio_academico = ($anio_actual - 1) . '-' . $anio_actual;
}
try {
    $sql = "SELECT COUNT(*) FROM cursos WHERE usuario_id = :usuario_id AND anio_academico = :anio_academico";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id, ':anio_academico' => $anio_academico]);
    $total_cursos_activos = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_cursos_activos = 0;
}

try {
    $stmt = $conexion->prepare("SELECT id, nombre_centro, poblacion, provincia, anio_academico, color FROM cursos WHERE usuario_id = :usuario_id AND anio_academico = :anio_academico ORDER BY id DESC LIMIT 6");
    $stmt->execute([':usuario_id' => $usuario_id, ':anio_academico' => $anio_academico]);
    $cursos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cursos_activos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educattio - Portal Inicio</title>
    
    <link rel="icon" type="image/png" href="../imagenes/dolphin.png">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/portal_inicio_usuario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-layout">
    
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        
        <header class="top-bar">
            <div class="welcome-text">
                <h1>Hola, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
                <p>Hoy es un buen día para evaluar.</p>
            </div>
            
            <div class="user-profile">
                <div class="notification-bell" id="notificationBell">
                    <i class="fas fa-bell"></i>
                    <span class="badge" id="notificationCount">0</span>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <h4>Próximos eventos (próximos 7 días)</h4>
                        <ul id="notificationList">
                            <li>Cargando...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <section class="dashboard-overview">
            <div class="overview-card courses-card">
                <div class="overview-card-header">
                    <div>
                        <span class="small-label">Cursos activos</span>
                        <h2><?php echo $total_cursos_activos; ?></h2>
                    </div>
                    <button class="btn-add-class" onclick="openModalCurso()"><i class="fas fa-plus"></i> Nuevo curso</button>
                </div>

                <div class="courses-list">
                    <?php if (empty($cursos_activos)): ?>
                        <p class="empty-state">No tienes cursos activos para este año académico.</p>
                    <?php else: ?>
                        <?php foreach ($cursos_activos as $curso): ?>
                            <a href="detalles_curso.php?id=<?php echo $curso['id']; ?>" class="small-course-card">
                                <span class="course-dot" style="background: <?php echo htmlspecialchars($curso['color'] ?: '#ff7a59'); ?>;"></span>
                                <div class="course-info">
                                    <strong><?php echo htmlspecialchars($curso['nombre_centro']); ?></strong>
                                    <span><?php echo htmlspecialchars($curso['poblacion'] . ', ' . $curso['provincia']); ?></span>
                                </div>
                                <span class="course-badge"><?php echo htmlspecialchars($curso['anio_academico']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="overview-card calendar-card">
                <div class="calendar-header-row">
                    <button class="calendar-nav" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                    <div class="calendar-title">
                        <span id="bigCalendarMonth">-- --</span>
                    </div>
                    <button class="calendar-nav" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="small-calendar-grid" id="calendarGrid">
                    <div class="day-name">Lun</div>
                    <div class="day-name">Mar</div>
                    <div class="day-name">Mié</div>
                    <div class="day-name">Jue</div>
                    <div class="day-name">Vie</div>
                    <div class="day-name">Sáb</div>
                    <div class="day-name">Dom</div>
                </div>
            </div>

            <div class="overview-card today-card">
                <div class="today-label">Hoy</div>
                <div class="today-day-name" id="current-day-name">--</div>
                <div class="today-number" id="current-day-number">--</div>
                <div class="today-month-year" id="current-month-year">--</div>
                <div class="today-clock" id="real-time-clock">--:--:--</div>
            </div>
        </section>

        <section class="classes-section">
            <div class="section-header">
                <h2>Mis cursos</h2>
                <button class="btn-add-class" onclick="openModalCurso()"><i class="fas fa-plus"></i> Añadir curso</button>
            </div>
            <div class="classes-grid">
                <?php if (empty($cursos_activos)): ?>
                    <div class="empty-state">Aún no has creado ningún curso. Pulsa "Añadir curso" para empezar.</div>
                <?php else: ?>
                    <?php foreach ($cursos_activos as $curso): ?>
                        <a href="detalles_curso.php?id=<?php echo $curso['id']; ?>" class="class-card">
                            <div class="card-banner" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($curso['color'] ?: '#ff7a59'); ?> 0%, rgba(255,255,255,0.14) 100%);">
                                <span class="subject-tag"><?php echo htmlspecialchars($curso['nombre_centro']); ?></span>
                                <span class="course-badge"><?php echo htmlspecialchars($curso['anio_academico']); ?></span>
                            </div>
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($curso['poblacion'] . ', ' . $curso['provincia']); ?></h3>
                                <p class="teacher-name">Curso activo en el año académico</p>
                                <div class="card-footer">
                                    <span class="tasks-pending">Ver detalles</span>
                                    <i class="fas fa-arrow-right folder-icon"></i>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<!-- ========================================= -->
<!-- MODAL PARA CREAR CURSO (COPIADO DE portal_cursos.php) -->
<!-- ========================================= -->
<div id="modalCurso" class="modal-overlay">
    <div class="modal-window">
        
        <div class="modal-header">
            <h3>Crear nuevo curso</h3>
            <button class="close-btn" onclick="closeModalCurso()">
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
                <button type="button" class="btn-cancel" onclick="closeModalCurso()">Cancelar</button>
                <button type="submit" class="btn-save" id="btnGuardarCurso">Guardar</button>
            </div>
        </form>

    </div>
</div>

<script src="../js/portal_inicio_usuario.js"></script>
<script src="../js/notificaciones.js"></script>

<script>
    // ========================
    // MANEJO DEL MODAL DE CURSO
    // ========================
    const modalCurso = document.getElementById('modalCurso');
    const formCurso = document.getElementById('formCrearCurso');

    window.openModalCurso = function() {
        if (modalCurso) modalCurso.style.display = 'flex';
        // Resetear formulario
        formCurso.reset();
        document.getElementById('editCursoId').value = '';
        document.getElementById('inputColor').value = '#ff7a59';
        // Resetear selección de color
        document.querySelectorAll('.color-dot').forEach(dot => dot.style.border = 'none');
    };

    window.closeModalCurso = function() {
        if (modalCurso) modalCurso.style.display = 'none';
    };

    // Funciones para los colores
    window.selectPresetColor = function(color, element) {
        document.getElementById('inputColor').value = color;
        document.querySelectorAll('.color-dot').forEach(dot => dot.style.border = 'none');
        if (element) element.style.border = '2px solid #333';
    };

    window.deselectPresets = function() {
        document.querySelectorAll('.color-dot').forEach(dot => dot.style.border = 'none');
    };

    // Envío del formulario vía AJAX
    formCurso.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            nombre_centro: document.getElementById('inputNombreCentro').value.trim(),
            poblacion: document.getElementById('inputPoblacion').value.trim(),
            provincia: document.getElementById('inputProvincia').value.trim(),
            anio: document.getElementById('inputAnio').value.trim(),
            color: document.getElementById('inputColor').value
        };

        fetch('../php/guardar_curso.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Curso creado correctamente');
                closeModalCurso();
                // Opcional: actualizar el contador de cursos activos sin recargar toda la página
                // Por ahora recargamos para que se actualice el número
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'No se pudo guardar el curso'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Error de conexión con el servidor');
        });
    });

    // Cerrar modal si se hace clic fuera
    window.onclick = function(event) {
        if (event.target === modalCurso) closeModalCurso();
    };

    
    // Calendario y reloj 
    function updateCalendarAndClock() {
        const now = new Date();
        const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        document.getElementById('current-day-name').textContent = days[now.getDay()];
        document.getElementById('current-day-number').textContent = now.getDate();
        document.getElementById('current-month-year').textContent = `${months[now.getMonth()]} ${now.getFullYear()}`;
        document.getElementById('real-time-clock').textContent = now.toLocaleTimeString('es-ES');
    }
    setInterval(updateCalendarAndClock, 1000);
    updateCalendarAndClock();

    // Cargar notificaciones de eventos próximos
    function cargarNotificaciones() {
        fetch('../php/obtener_notificaciones.php')
            .then(res => res.json())
            .then(data => {
                const count = data.length;
                document.getElementById('notificationCount').innerText = count;
                const list = document.getElementById('notificationList');
                list.innerHTML = '';
                if (count === 0) {
                    list.innerHTML = '<li>No hay eventos próximos</li>';
                } else {
                    data.forEach(ev => {
                        const fecha = new Date(ev.fecha);
                        const fechaStr = fecha.toLocaleDateString('es-ES');
                        const li = document.createElement('li');
                        li.innerHTML = `<strong>${escapeHtml(ev.titulo)}</strong> <small>${fechaStr}</small><br><span style="font-size:0.75rem;">${ev.tipo_evento}</span>`;
                        list.appendChild(li);
                    });
                }
            })
            .catch(err => {
                console.error('Error cargando notificaciones:', err);
                document.getElementById('notificationList').innerHTML = '<li>Error al cargar</li>';
            });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Cargar cada 60 segundos
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 60000);
</script>

</body>
</html>