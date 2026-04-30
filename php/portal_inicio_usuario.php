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
    <!-- Añadimos el CSS del modal de cursos (para que se vea igual que en portal_cursos) -->
    <link rel="stylesheet" href="../css/portal_cursos.css">
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
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e1f5fe; color: #039be5;">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_cursos_activos; ?></h3>
                        <p>Cursos Activos</p>
                    </div>
                </div>
            </div>

                <?php 
                    $widget_show_events = true;
                    $widget_title = 'Eventos próximos';
                    include 'calendar_widget.php'; 
                ?>    
        </section>

        <section class="classes-section">
            <!-- Aquí podrías listar los cursos reales del usuario (similar a portal_cursos.php) -->
            <div class="classes-grid" id="cursos-grid">
                <!-- Se llenará dinámicamente con JS o con PHP, pero por ahora vacío -->
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

<script src="../js/portal_cursos.js"></script>
<script src="../js/portal_inicio_usuario.js"></script>
<script src="../js/calendario.js"></script>

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