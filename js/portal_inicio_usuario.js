document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. RELOJ Y FECHA DEL WIDGET SUPERIOR (PEQUEÑO) ---
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
    
    // --- 2. LÓGICA DEL CALENDARIO GRANDE ---
    let currentDate = new Date(); // Fecha para navegar

    function renderBigCalendar() {
        const monthDisplay = document.getElementById('bigCalendarMonth');
        const grid = document.getElementById('calendarGrid');
        
        // Si no existe el calendario en esta página, salir
        if (!grid || !monthDisplay) return;

        // Limpiar días anteriores pero manteniendo los encabezados (Lun, Mar...)
        const headers = Array.from(grid.children).slice(0, 7);
        grid.innerHTML = '';
        headers.forEach(header => grid.appendChild(header));

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        monthDisplay.textContent = `${months[month]} ${year}`;

        // Obtener el primer día del mes (0=Domingo, 1=Lunes...)
        const firstDayIndex = new Date(year, month, 1).getDay();
        const lastDay = new Date(year, month + 1, 0).getDate();
        
        // Ajustar para que el calendario empiece en Lunes
        // En JS: Domingo=0. Queremos: Lunes=0 ... Domingo=6
        let adjustedFirstDay = firstDayIndex === 0 ? 6 : firstDayIndex - 1;

        // Rellenar huecos vacíos antes del primer día
        for (let i = 0; i < adjustedFirstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.classList.add('day-cell', 'empty');
            emptyCell.style.background = 'transparent';
            emptyCell.style.border = 'none';
            emptyCell.style.cursor = 'default';
            grid.appendChild(emptyCell);
        }

        // Crear los días
        const today = new Date();
        for (let i = 1; i <= lastDay; i++) {
            const cell = document.createElement('div');
            cell.classList.add('day-cell');
            
            // Marcar el día de hoy si coincide
            if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                cell.classList.add('today');
            }

            cell.innerHTML = `<span class="day-number">${i}</span>`;
            
            // Ejemplo: Evento aleatorio (puedes quitar esto luego)
            if (Math.random() > 0.8) {
                cell.innerHTML += `<div class="event-dot"></div>`;
            }

            grid.appendChild(cell);
        }
    }

    // Eventos botones Anterior / Siguiente mes
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderBigCalendar();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderBigCalendar();
        });
    }

    // Inicializar todo
    updateWidget();
    setInterval(updateWidget, 1000);
    renderBigCalendar();
});