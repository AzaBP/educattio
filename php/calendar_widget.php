<?php
// Este archivo NO debe llamar a session_start().
// Se espera que $usuario_id ya esté definido desde la página principal.
// Si se accede directamente sin variable, se intenta obtener de la sesión global (pero mejor pasar parámetro).
if (!isset($usuario_id) && isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
}
if (!isset($usuario_id)) {
    echo '<div class="calendar-error">Error: usuario no identificado</div>';
    return;
}

// Guardamos el nombre en una variable para usarlo abajo
$nombre_usuario = $_SESSION['nombre_usuario'];

// Guardamos el nombre en una variable para usarlo abajo
$usuario_id = $_SESSION['usuario_id'];
// Obtener mes y año desde parámetros GET (para navegación)
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Ajustar bordes
if ($currentMonth < 1) {
    $currentMonth = 12;
    $currentYear--;
}
if ($currentMonth > 12) {
    $currentMonth = 1;
    $currentYear++;
}

// Conexión a BD (asumiendo que 'conexion.php' está en el mismo directorio o en ruta)
require_once 'conexion.php'; 

$startDate = date('Y-m-01', strtotime("$currentYear-$currentMonth-01"));
$endDate = date('Y-m-t', strtotime("$startDate"));

$stmt = $conexion->prepare("SELECT id, titulo, DATE(fecha) as fecha, tipo_evento 
                            FROM eventos 
                            WHERE usuario_id = :uid AND fecha BETWEEN :start AND :end");
$stmt->execute([':uid' => $usuario_id, ':start' => $startDate, ':end' => $endDate]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Indexar eventos por fecha
$eventosPorFecha = [];
foreach ($eventos as $ev) {
    $eventosPorFecha[$ev['fecha']][] = $ev;
}

$monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$firstDayWeek = date('N', strtotime($startDate)); // 1=Lunes, 7=Domingo
$daysInMonth = date('t', strtotime($startDate));
$today = date('Y-m-d');
?>

<div class="custom-calendar-widget" id="dynamicCalendarWidget">
    <div class="calendar-header">
        <button class="calendar-nav" data-month="<?php echo $currentMonth-1; ?>" data-year="<?php echo $currentYear; ?>">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h3><?php echo $monthNames[$currentMonth-1] . ' ' . $currentYear; ?></h3>
        <button class="calendar-nav" data-month="<?php echo $currentMonth+1; ?>" data-year="<?php echo $currentYear; ?>">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <div class="calendar-grid">
        <div class="day-header">L</div><div class="day-header">M</div><div class="day-header">X</div>
        <div class="day-header">J</div><div class="day-header">V</div><div class="day-header">S</div><div class="day-header">D</div>

        <?php
        // Celdas vacías iniciales
        for ($i = 1; $i < $firstDayWeek; $i++) {
            echo '<div class="calendar-day empty"></div>';
        }
        // Días del mes
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $fecha = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $d);
            $esHoy = ($fecha === $today);
            $tieneEventos = isset($eventosPorFecha[$fecha]);
            $clase = 'calendar-day';
            if ($esHoy) $clase .= ' today';
            if ($tieneEventos) $clase .= ' has-events';
            echo "<div class='$clase' data-fecha='$fecha'>";
            echo "<span class='day-number'>$d</span>";
            if ($tieneEventos) {
                echo "<span class='event-dot'></span>";
            }
            echo "</div>";
        }
        ?>
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
// Manejar navegación por AJAX para no recargar toda la página
document.querySelectorAll('.calendar-nav').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const month = this.dataset.month;
        const year = this.dataset.year;
        const container = document.getElementById('dynamicCalendarWidget').parentNode;
        fetch('calendar_widget.php?month=' + month + '&year=' + year)
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => console.error('Error al cargar calendario:', err));
    });
});

// Mostrar eventos al hacer clic en un día
document.querySelectorAll('.calendar-day:not(.empty)').forEach(day => {
    day.addEventListener('click', function() {
        const fecha = this.dataset.fecha;
        if (fecha) {
            // Llamar a función global para mostrar modal de eventos (definir en main page)
            if (typeof window.showEventModal === 'function') {
                window.showEventModal(fecha);
            } else {
                console.warn('Función showEventModal no definida');
            }
        }
    });
});
</script>