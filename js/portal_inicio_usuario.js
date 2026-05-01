document.addEventListener('DOMContentLoaded', function() {
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
    let currentDate = new Date();

    function renderSmallCalendar() {
        const monthDisplay = document.getElementById('bigCalendarMonth');
        const grid = document.getElementById('calendarGrid');
        if (!grid || !monthDisplay) return;

        const headers = Array.from(grid.children).slice(0, 7);
        grid.innerHTML = '';
        headers.forEach(header => grid.appendChild(header));

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        monthDisplay.textContent = `${months[month]} ${year}`;

        const firstDayIndex = new Date(year, month, 1).getDay();
        const lastDay = new Date(year, month + 1, 0).getDate();
        const adjustedFirstDay = firstDayIndex === 0 ? 6 : firstDayIndex - 1;

        for (let i = 0; i < adjustedFirstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('day-cell', 'empty');
            emptyCell.style.background = 'transparent';
            emptyCell.style.border = 'none';
            emptyCell.style.cursor = 'default';
            grid.appendChild(emptyCell);
        }

        const today = new Date();
        for (let i = 1; i <= lastDay; i++) {
            const cell = document.createElement('div');
            cell.classList.add('day-cell');
            if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                cell.classList.add('today');
            }
            cell.innerHTML = `<span class="day-number">${i}</span>`;
            grid.appendChild(cell);
        }
    }

    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderSmallCalendar();
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderSmallCalendar();
        });
    }

    updateWidget();
    setInterval(updateWidget, 1000);
    renderSmallCalendar();

    document.addEventListener('click', function(event) {
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
