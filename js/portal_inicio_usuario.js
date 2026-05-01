document.addEventListener('DOMContentLoaded', function () {
    // --- 1. RELOJ Y FECHA DEL WIDGET SUPERIOR ---
    function updateWidget() {
        const now = new Date();
        const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        const dayNameEl = document.getElementById('current-day-name');
        const dayNumEl = document.getElementById('current-day-number');
        const monthYearEl = document.getElementById('current-month-year');
        const clockEl = document.getElementById('real-time-clock');

        if (dayNameEl) dayNameEl.textContent = days[now.getDay()];
        if (dayNumEl) dayNumEl.textContent = now.getDate();
        if (monthYearEl) monthYearEl.textContent = `${months[now.getMonth()]} ${now.getFullYear()}`;
        if (clockEl) {
            clockEl.textContent = now.toLocaleTimeString('es-ES', {
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        }
    }

    // --- 2. CALENDARIO PEQUEÑO ---
    if (window.MiniCalendar) {
        window.miniCalendarInicio = new MiniCalendar('#miniCalendarContainer', {
            onEventCreate: () => {
                if (typeof cargarNotificaciones === 'function') {
                    cargarNotificaciones();
                }
            }
        });
    }

    updateWidget();
    setInterval(updateWidget, 1000);

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.menu-btn') && !event.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => menu.classList.remove('show'));
        }
        const modal = document.getElementById('modalCurso');
        if (modal && event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

function toggleMenu(menuId) {
    const menu = document.getElementById(menuId);
    if (!menu) return;
    document.querySelectorAll('.dropdown-menu').forEach(m => {
        if (m.id !== menuId) m.classList.remove('show');
    });
    menu.classList.toggle('show');
}
