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

function toggleMenu(menuId) {
    const menu = document.getElementById(menuId);
    
    // Cierra otros menús abiertos para que no se solapen
    const allMenus = document.querySelectorAll('.dropdown-menu');
    allMenus.forEach(m => {
        if (m.id !== menuId) {
            m.classList.remove('show');
        }
    });

    // Abre o cierra el actual
    menu.classList.toggle('show');
}

// Cerrar el menú si se hace clic fuera de él
window.onclick = function(event) {
    if (!event.target.matches('.menu-btn') && !event.target.matches('.menu-btn i')) {
        const dropdowns = document.getElementsByClassName("dropdown-menu");
        for (let i = 0; i < dropdowns.length; i++) {
            let openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}

/* --- FUNCIONES PARA LA VENTANA MODAL --- */

function openModal() {
    const modal = document.getElementById('modalCurso');
    modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('modalCurso');
    modal.classList.remove('active');
}

/* Cerrar el modal si se hace clic fuera de la ventana blanca (en el fondo oscuro) */
window.onclick = function(event) {
    const modal = document.getElementById('modalCurso');
    
    // Lógica existente para los menús de 3 puntos
    if (!event.target.matches('.menu-btn') && !event.target.matches('.menu-btn i')) {
        const dropdowns = document.getElementsByClassName("dropdown-menu");
        for (let i = 0; i < dropdowns.length; i++) {
            let openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }

    // NUEVA Lógica: Cerrar modal al hacer clic fuera
    if (event.target == modal) {
        closeModal();
    }
}

/* ==========================================
   FUNCIÓN PARA CREAR CURSO DINÁMICAMENTE
   ========================================== */
function guardarNuevoCurso(e) {
    e.preventDefault(); // Evita que se recargue la página al enviar el form

    // 1. Capturamos los datos del formulario (suponiendo que tienes inputs con estos IDs)
    const nombre = document.getElementById('inputNombreCurso').value;
    const subtitulo = document.getElementById('inputSubtitulo').value; // Ej: Tutoría, Mates...
    
    // Generamos un ID único para el menú (para que no se abran todos a la vez)
    const uniqueId = Date.now(); 

    // 2. Creamos el HTML de la tarjeta CON EL ENLACE INCLUIDO
    // Nota el onclick en el primer div y el stopPropagation en el botón
    const nuevaTarjetaHTML = `
        <article class="class-card" onclick="window.location.href='detalle_curso.html'">
            
            <div class="card-banner banner-math"> <span class="subject-tag">${nombre}</span>
                
                <div class="card-menu">
                    <button class="menu-btn" onclick="event.stopPropagation(); toggleMenu('menu-${uniqueId}')">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    
                    <div id="menu-${uniqueId}" class="dropdown-menu" onclick="event.stopPropagation()">
                        <a href="#"><i class="fas fa-pen"></i> Modificar</a>
                        <a href="#" class="delete-option"><i class="fas fa-trash"></i> Eliminar</a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <h3>${subtitulo}</h3> <p class="teacher-name">Curso Nuevo</p>
                <div class="card-footer">
                    <span class="tasks-pending">0 tareas pendientes</span>
                    <i class="fas fa-folder-open folder-icon"></i>
                </div>
            </div>

        </article>
    `;

    // 3. Insertamos la tarjeta en la rejilla (antes del botón de añadir)
    // Asegúrate de que tu contenedor en HTML tenga id="gridCursos" o ajusta este selector
    const contenedor = document.querySelector('.dashboard-grid'); 
    
    // Insertamos la tarjeta nueva al principio o antes del botón "Añadir"
    // (Ajusta esto según dónde quieras que aparezca)
    contenedor.insertAdjacentHTML('afterbegin', nuevaTarjetaHTML);

    // 4. Cerramos el modal y limpiamos
    closeModal(); 
    document.getElementById('formCrearCurso').reset();
}