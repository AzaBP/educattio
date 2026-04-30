<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo '<div class="calendar-error">Sesión no válida</div>';
    exit();
}

$userId = $_SESSION['usuario_id'];

// Obtener eventos del mes actual
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Ajustar mes/año si es necesario
if ($currentMonth < 1) {
    $currentMonth = 12;
    $currentYear--;
} elseif ($currentMonth > 12) {
    $currentMonth = 1;
    $currentYear++;
}

// Obtener eventos del mes
$startDate = date('Y-m-01', strtotime("$currentYear-$currentMonth-01"));
$endDate = date('Y-m-t', strtotime("$currentYear-$currentMonth-01"));

$stmt = $conexion->prepare("SELECT id, titulo, fecha, tipo_evento FROM eventos
                            WHERE usuario_id = :uid AND fecha BETWEEN :start AND :end
                            ORDER BY fecha ASC");
$stmt->execute([':uid' => $userId, ':start' => $startDate, ':end' => $endDate]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar eventos por fecha
$eventosPorFecha = [];
foreach ($eventos as $evento) {
    $fecha = date('Y-m-d', strtotime($evento['fecha']));
    if (!isset($eventosPorFecha[$fecha])) {
        $eventosPorFecha[$fecha] = [];
    }
    $eventosPorFecha[$fecha][] = $evento;
}

// Función para obtener nombre del mes
function getMonthName($month) {
    $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return $months[$month - 1];
}

// Generar calendario
$firstDayOfMonth = date('N', strtotime("$currentYear-$currentMonth-01")); // 1=Lunes, 7=Domingo
$daysInMonth = date('t', strtotime("$currentYear-$currentMonth-01"));
$today = date('Y-m-d');
?>

<div class="calendar-container">
    <div class="calendar-header">
        <button class="calendar-nav" onclick="changeMonth(<?php echo $currentMonth-1; ?>, <?php echo $currentYear; ?>)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h3><?php echo getMonthName($currentMonth) . ' ' . $currentYear; ?></h3>
        <button class="calendar-nav" onclick="changeMonth(<?php echo $currentMonth+1; ?>, <?php echo $currentYear; ?>)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <div class="calendar-grid">
        <!-- Días de la semana -->
        <div class="day-header">L</div>
        <div class="day-header">M</div>
        <div class="day-header">X</div>
        <div class="day-header">J</div>
        <div class="day-header">V</div>
        <div class="day-header">S</div>
        <div class="day-header">D</div>

        <?php
        // Espacios vacíos antes del primer día
        for ($i = 1; $i < $firstDayOfMonth; $i++) {
            echo '<div class="calendar-day empty"></div>';
        }

        // Días del mes
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            $isToday = ($currentDate === $today);
            $hasEvents = isset($eventosPorFecha[$currentDate]);

            $classes = 'calendar-day';
            if ($isToday) $classes .= ' today';
            if ($hasEvents) $classes .= ' has-events';

            echo "<div class='$classes' data-date='$currentDate'>";
            echo "<span class='day-number'>$day</span>";
            if ($hasEvents) {
                echo "<div class='event-indicator'>" . count($eventosPorFecha[$currentDate]) . "</div>";
            }
            echo "</div>";
        }
        ?>
    </div>
</div>

<!-- Modal para mostrar eventos del día -->
<div id="eventModal" class="event-modal">
    <div class="event-modal-content">
        <div class="event-modal-header">
            <h4 id="modalDate">Eventos del día</h4>
            <button class="event-modal-close" onclick="closeEventModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="eventList" class="event-list">
            <!-- Eventos se cargarán aquí -->
        </div>
    </div>
</div>

<style>
.calendar-container {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    width: 100%;
    max-width: 400px;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.calendar-header h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.calendar-nav {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #666;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
}

.calendar-nav:hover {
    background: #f5f5f5;
    color: #333;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.day-header {
    text-align: center;
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
    padding: 8px 0;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.calendar-day:hover {
    background: #f8f9fa;
    border-color: #e9ecef;
}

.calendar-day.today {
    background: rgba(250, 82, 82, 0.1);
    border-color: var(--accent-color, #fa5252);
    color: var(--accent-color, #fa5252);
    font-weight: 600;
}

.calendar-day.has-events {
    background: rgba(76, 175, 80, 0.1);
}

.calendar-day.has-events:hover {
    background: rgba(76, 175, 80, 0.15);
}

.day-number {
    font-size: 0.9rem;
    font-weight: 500;
}

.event-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 6px;
    height: 6px;
    background: #4caf50;
    border-radius: 50%;
    font-size: 0.6rem;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.calendar-day.empty {
    cursor: default;
}

.calendar-day.empty:hover {
    background: transparent;
    border-color: transparent;
}

/* Modal de eventos */
.event-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.event-modal.show {
    display: flex;
}

.event-modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 400px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.event-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.event-modal-header h4 {
    margin: 0;
    font-size: 1.1rem;
    color: #333;
}

.event-modal-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #666;
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    transition: all 0.2s;
}

.event-modal-close:hover {
    background: #f5f5f5;
    color: #333;
}

.event-list {
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.event-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.event-item:last-child {
    border-bottom: none;
}

.event-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.event-type {
    font-size: 0.85rem;
    color: #666;
    background: #f5f5f5;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
}

.no-events {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px 0;
}

@media (max-width: 480px) {
    .calendar-container {
        max-width: 100%;
        padding: 15px;
    }

    .calendar-header h3 {
        font-size: 1rem;
    }

    .calendar-nav {
        font-size: 1rem;
        padding: 6px;
    }

    .day-header {
        font-size: 0.8rem;
        padding: 6px 0;
    }

    .calendar-day {
        min-height: 35px;
    }

    .day-number {
        font-size: 0.8rem;
    }

    .event-modal-content {
        width: 95%;
        margin: 20px;
    }

    .event-modal-header,
    .event-list {
        padding: 15px;
    }
}
</style>

<script>
function changeMonth(month, year) {
    const url = new URL(window.location);
    url.searchParams.set('month', month);
    url.searchParams.set('year', year);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para días del calendario
    document.querySelectorAll('.calendar-day:not(.empty)').forEach(day => {
        day.addEventListener('click', function() {
            const date = this.dataset.date;
            showEventsForDate(date);
        });
    });
});

function showEventsForDate(date) {
    const modal = document.getElementById('eventModal');
    const modalDate = document.getElementById('modalDate');
    const eventList = document.getElementById('eventList');

    // Formatear fecha para mostrar
    const dateObj = new Date(date);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    modalDate.textContent = 'Eventos del ' + dateObj.toLocaleDateString('es-ES', options);

    // Obtener eventos de esta fecha (desde PHP)
    fetch(`obtener_eventos_fecha.php?fecha=${date}`)
        .then(res => res.json())
        .then(data => {
            eventList.innerHTML = '';

            if (data.length === 0) {
                eventList.innerHTML = '<div class="no-events">No hay eventos programados para este día</div>';
            } else {
                data.forEach(evento => {
                    const eventItem = document.createElement('div');
                    eventItem.className = 'event-item';
                    eventItem.innerHTML = `
                        <div class="event-title">${escapeHtml(evento.titulo)}</div>
                        <div class="event-type">${escapeHtml(evento.tipo_evento)}</div>
                    `;
                    eventList.appendChild(eventItem);
                });
            }

            modal.classList.add('show');
        })
        .catch(err => {
            console.error('Error cargando eventos:', err);
            eventList.innerHTML = '<div class="no-events">Error al cargar eventos</div>';
            modal.classList.add('show');
        });
}

function closeEventModal() {
    document.getElementById('eventModal').classList.remove('show');
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
}

// Cerrar modal al hacer clic fuera
document.getElementById('eventModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEventModal();
    }
});
</script>