<?php
// Parámetros opcionales
if (!isset($widget_show_events)) $widget_show_events = true;
if (!isset($widget_title)) $widget_title = 'Próximos eventos';
?>
<div class="custom-calendar-widget" onclick="window.location.href='calendario.php'">
    <div class="widget-date">
        <div class="widget-day-name" id="widgetDayName">--</div>
        <div class="widget-day-number" id="widgetDayNumber">--</div>
        <div class="widget-month-year" id="widgetMonthYear">--</div>
        <div class="widget-clock"><i class="far fa-clock"></i> <span id="widgetClock">--:--:--</span></div>
    </div>
    <?php if ($widget_show_events): ?>
    <div class="widget-events">
        <div class="widget-events-header">
            <i class="fas fa-calendar-week"></i> <?php echo htmlspecialchars($widget_title); ?>
        </div>
        <ul class="widget-events-list" id="widgetEventsList">
            <li>Cargando...</li>
        </ul>
    </div>
    <?php endif; ?>
</div>

<style>
.custom-calendar-widget {
    background: white;
    border-radius: 24px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    flex: 1;
    min-width: 250px;
}
.custom-calendar-widget:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.widget-date {
    text-align: center;
    min-width: 140px;
}
.widget-day-name {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--accent-color, #fa5252);
}
.widget-day-number {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}
.widget-month-year {
    font-size: 0.9rem;
    color: #666;
}
.widget-clock {
    font-size: 0.9rem;
    margin-top: 5px;
    color: #555;
}
.widget-events {
    flex: 1;
    min-width: 200px;
}
.widget-events-header {
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}
.widget-events-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.widget-events-list li {
    border-bottom: 1px solid #eee;
    padding: 8px 0;
}
.widget-events-list li:last-child {
    border-bottom: none;
}
.widget-event-title {
    font-weight: 500;
    font-size: 0.9rem;
}
.widget-event-date {
    font-size: 0.75rem;
    color: #777;
}
@media (max-width: 640px) {
    .custom-calendar-widget {
        flex-direction: column;
        text-align: center;
    }
    .widget-events {
        width: 100%;
    }
}
</style>

<script>
(function() {
    function updateWidgetDateTime() {
        const now = new Date();
        const days = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        const months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        const dayNameEl = document.getElementById('widgetDayName');
        const dayNumEl = document.getElementById('widgetDayNumber');
        const monthYearEl = document.getElementById('widgetMonthYear');
        const clockEl = document.getElementById('widgetClock');
        if (dayNameEl) dayNameEl.textContent = days[now.getDay()];
        if (dayNumEl) dayNumEl.textContent = now.getDate();
        if (monthYearEl) monthYearEl.textContent = `${months[now.getMonth()]} ${now.getFullYear()}`;
        if (clockEl) clockEl.textContent = now.toLocaleTimeString('es-ES');
    }
    setInterval(updateWidgetDateTime, 1000);
    updateWidgetDateTime();

    <?php if ($widget_show_events): ?>
    function loadWidgetEvents() {
        const listContainer = document.getElementById('widgetEventsList');
        if (!listContainer) return;
        fetch('../php/obtener_notificaciones.php')
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    listContainer.innerHTML = '<li>No hay eventos próximos</li>';
                } else {
                    listContainer.innerHTML = '';
                    data.slice(0, 5).forEach(ev => {
                        const fecha = new Date(ev.fecha);
                        const fechaStr = fecha.toLocaleDateString('es-ES');
                        const li = document.createElement('li');
                        li.innerHTML = `<div class="widget-event-title">${escapeHtml(ev.titulo)}</div>
                                        <div class="widget-event-date">${fechaStr} · ${ev.tipo_evento}</div>`;
                        listContainer.appendChild(li);
                    });
                }
            })
            .catch(err => {
                console.error('Error cargando eventos widget:', err);
                listContainer.innerHTML = '<li>Error al cargar eventos</li>';
            });
    }
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
    }
    loadWidgetEvents();
    setInterval(loadWidgetEvents, 60000);
    <?php endif; ?>
})();
</script>