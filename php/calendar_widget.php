<?php
// calendar_widget.php - Versión modificada para filtrar por curso (si $widget_curso_id está definido)

// No iniciar sesión aquí; se espera que la página principal ya tenga la sesión iniciada.
if (!isset($usuario_id) && isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
}
if (!isset($usuario_id)) {
    echo '<div class="calendar-error">Error: usuario no identificado</div>';
    return;
}

// Determinar si se debe filtrar por un curso específico (variable pasada antes de incluir)
$curso_id = isset($widget_curso_id) ? (int)$widget_curso_id : 0;

// Obtener mes y año desde parámetros GET (para navegación)
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($currentMonth < 1) {
    $currentMonth = 12;
    $currentYear--;
}
if ($currentMonth > 12) {
    $currentMonth = 1;
    $currentYear++;
}

require_once 'conexion.php';

$startDate = date('Y-m-01', strtotime("$currentYear-$currentMonth-01"));
$endDate = date('Y-m-t', strtotime("$startDate"));

// Consulta de eventos según si hay curso específico o no
if ($curso_id > 0) {
    // Obtener eventos de las clases que pertenecen a este curso
    $sql = "SELECT e.id, e.titulo, DATE(e.fecha) as fecha, e.tipo_evento 
            FROM eventos e
            JOIN clases c ON e.clase_id = c.id
            WHERE c.curso_id = :curso_id AND e.fecha BETWEEN :start AND :end";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id, ':start' => $startDate, ':end' => $endDate]);
} else {
    // Eventos generales del usuario (sin clase específica o con clase_id NULL)
    $sql = "SELECT id, titulo, DATE(fecha) as fecha, tipo_evento 
            FROM eventos 
            WHERE usuario_id = :uid AND fecha BETWEEN :start AND :end";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':uid' => $usuario_id, ':start' => $startDate, ':end' => $endDate]);
}
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Indexar eventos por fecha
$eventosPorFecha = [];
foreach ($eventos as $ev) {
    $eventosPorFecha[$ev['fecha']][] = $ev;
}

$monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$firstDayWeek = date('N', strtotime($startDate));
$daysInMonth = date('t', strtotime($startDate));
$today = date('Y-m-d');
?>

<div class="custom-calendar-widget" id="dynamicCalendarWidget">
    <div class="calendar-header">
        <button class="calendar-nav" data-month="<?php echo $currentMonth-1; ?>" data-year="<?php echo $currentYear; ?>" data-curso="<?php echo $curso_id; ?>">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h3><?php echo $monthNames[$currentMonth-1] . ' ' . $currentYear; ?></h3>
        <button class="calendar-nav" data-month="<?php echo $currentMonth+1; ?>" data-year="<?php echo $currentYear; ?>" data-curso="<?php echo $curso_id; ?>">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <div class="calendar-grid">
        <div class="day-header">L</div><div class="day-header">M</div><div class="day-header">X</div>
        <div class="day-header">J</div><div class="day-header">V</div><div class="day-header">S</div><div class="day-header">D</div>

        <?php
        for ($i = 1; $i < $firstDayWeek; $i++) {
            echo '<div class="calendar-day empty"></div>';
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $fecha = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $d);
            $esHoy = ($fecha === $today);
            $tieneEventos = isset($eventosPorFecha[$fecha]);
            $clase = 'calendar-day';
            if ($esHoy) $clase .= ' today';
            if ($tieneEventos) $clase .= ' has-events';
            echo "<div class='$clase' data-fecha='$fecha' data-curso='$curso_id'>";
            echo "<span class='day-number'>$d</span>";
            if ($tieneEventos) {
                echo "<span class='event-dot'></span>";
            }
            echo "</div>";
        }
        ?>
    </div>
</div>

<!-- Modal para mostrar detalles de eventos (se mantiene igual, solo se mejora la carga) -->
<div id="eventDetailModal" class="modal-overlay" style="display:none;">
    <div class="modal-window" style="max-width: 520px;">
        <div class="modal-header">
            <h3>Eventos del día</h3>
            <button class="close-btn" onclick="hideEventDetailModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="eventDetailContent">
            <p class="text-muted">Selecciona una fecha con eventos para ver más detalles.</p>
        </div>
        <div class="modal-footer" style="justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="hideEventDetailModal()">Cerrar</button>
        </div>
    </div>
</div>

<style>
.custom-calendar-widget {
    background: white;
    border-radius: 16px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    width: 100%;
    max-width: 320px;
}
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.calendar-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}
.calendar-nav {
    background: none;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 20px;
    transition: background 0.2s;
    color: #666;
}
.calendar-nav:hover {
    background: #f0f0f0;
    color: #333;
}
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 3px;
}
.day-header {
    text-align: center;
    font-weight: 600;
    font-size: 0.75rem;
    color: #666;
    padding-bottom: 6px;
}
.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: #f9f9f9;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
    font-size: 0.8rem;
    font-weight: 500;
    min-height: 28px;
}
.calendar-day.empty {
    background: transparent;
    cursor: default;
}
.calendar-day:not(.empty):hover {
    background: #eaeaea;
    transform: scale(0.95);
}
.calendar-day.today {
    background: rgba(250, 82, 82, 0.2);
    font-weight: bold;
    color: #fa5252;
}
.calendar-day.has-events {
    background: rgba(76, 175, 80, 0.15);
}
.event-dot {
    position: absolute;
    bottom: 2px;
    width: 4px;
    height: 4px;
    background: #4caf50;
    border-radius: 50%;
}
@media (max-width: 640px) {
    .custom-calendar-widget { 
        padding: 12px;
        max-width: 280px;
    }
    .calendar-grid { gap: 2px; }
    .calendar-day {
        min-height: 24px;
        font-size: 0.75rem;
    }
}
</style>

<script>
// Función para recargar el widget mediante AJAX (preserva el curso_id si existe)
function reloadCalendarWidget(month, year, cursoId) {
    const container = document.getElementById('dynamicCalendarWidget').parentNode;
    let url = 'calendar_widget.php?month=' + month + '&year=' + year;
    if (cursoId && cursoId > 0) {
        url += '&curso_id=' + cursoId;
    }
    fetch(url)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
            // Reasignar eventos después de insertar el nuevo contenido
            bindNavButtons();
            bindDayClick();
        })
        .catch(err => console.error('Error al cargar calendario:', err));
}

// Asignar eventos a los botones de navegación (se ejecuta cada vez que se recarga el widget)
function bindNavButtons() {
    document.querySelectorAll('.calendar-nav').forEach(btn => {
        // Evitar duplicar eventos
        btn.removeEventListener('click', navClickHandler);
        btn.addEventListener('click', navClickHandler);
    });
}

function navClickHandler(e) {
    e.stopPropagation();
    const month = this.dataset.month;
    const year = this.dataset.year;
    const cursoId = this.dataset.curso || 0;
    reloadCalendarWidget(month, year, cursoId);
}

// Asignar eventos a los días para mostrar modal
function bindDayClick() {
    document.querySelectorAll('.calendar-day:not(.empty)').forEach(day => {
        day.removeEventListener('click', dayClickHandler);
        day.addEventListener('click', dayClickHandler);
    });
}

function dayClickHandler(e) {
    const fecha = this.dataset.fecha;
    const cursoId = this.dataset.curso || 0;
    if (fecha) {
        showEventModal(fecha, cursoId);
    }
}

// Función global para mostrar eventos de una fecha (puede llamarse desde fuera)
window.showEventModal = async function(fecha, cursoId = 0) {
    const modal = document.getElementById('eventDetailModal');
    const content = document.getElementById('eventDetailContent');
    if (!modal || !content) return;
    content.innerHTML = '<div class="p-3 text-center">Cargando eventos...</div>';
    modal.style.display = 'flex';

    try {
        let url = 'obtener_eventos_fecha.php?fecha=' + encodeURIComponent(fecha);
        if (cursoId && cursoId > 0) {
            url += '&curso_id=' + cursoId;
        }
        const res = await fetch(url);
        const eventos = await res.json();
        if (!eventos.length) {
            content.innerHTML = '<p class="text-muted">No hay eventos para esta fecha.</p>';
            return;
        }
        content.innerHTML = eventos.map(evento => {
            return `
                <div class="event-detail-card" style="border:1px solid #e2e8f0;border-radius:14px;padding:16px;margin-bottom:12px;">
                    <h4 style="margin:0 0 8px;">${escapeHtml(evento.titulo)}</h4>
                    <p style="margin:0 6px 10px; color:#4b5563; font-size:0.95rem;"><strong>Tipo:</strong> ${escapeHtml(evento.tipo_evento)}</p>
                    <p style="margin:0 6px 10px; color:#4b5563; font-size:0.95rem;"><strong>Centro:</strong> ${escapeHtml(evento.nombre_centro || 'General')}<br>
                        <strong>Curso:</strong> ${escapeHtml(evento.anio_academico || 'N/A')}<br>
                        <strong>Clase:</strong> ${escapeHtml(evento.nombre_clase || 'General')}<br>
                        <strong>Asignatura:</strong> ${escapeHtml(evento.materia_principal || 'N/D')}</p>
                    <p style="margin:0 6px 0; color:#334155; font-size:0.95rem;"><strong>Descripción:</strong><br>${escapeHtml(evento.descripcion || 'Sin descripción')}</p>
                </div>
            `;
        }).join('');
    } catch (err) {
        content.innerHTML = '<p class="text-danger">Error cargando eventos.</p>';
        console.error(err);
    }
};

window.hideEventDetailModal = function() {
    const modal = document.getElementById('eventDetailModal');
    if (modal) modal.style.display = 'none';
};

function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"]/g, function(match) {
        const escape = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
        return escape[match] || match;
    });
}

// Inicializar eventos después de cargar el DOM (y también después de una recarga AJAX)
document.addEventListener('DOMContentLoaded', () => {
    bindNavButtons();
    bindDayClick();
});
// Para recargas internas (cuando se reemplaza el HTML), se ejecutará el script del nuevo contenido.
// Para asegurar que los eventos se vuelvan a asignar, se puede llamar desde el script que recarga.
// Por eso hemos definido bindNavButtons y bindDayClick como funciones y las llamamos al inicio.
</script>