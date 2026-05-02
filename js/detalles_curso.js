
/* ==========================================
   INTERACTIVIDAD DE DETALLES DEL CURSO
   ========================================== */

function actualizarReloj() {
    const ahora = new Date();
    const dName = document.getElementById('current-day-name');
    const dNumber = document.getElementById('current-day-number');
    const mYear = document.getElementById('current-month-year');
    const clock = document.getElementById('real-time-clock');

    if(dName) dName.innerText = ahora.toLocaleDateString('es-ES', { weekday: 'long' }).toUpperCase();
    if(dNumber) dNumber.innerText = ahora.getDate();
    if(mYear) mYear.innerText = ahora.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' }).toUpperCase();
    if(clock) clock.innerText = ahora.toLocaleTimeString('es-ES');
}

document.addEventListener('DOMContentLoaded', () => {
    // Iniciar Reloj
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
    
    // Iniciar Mini Calendario
    const cursoId = document.getElementById('cursoIdAsociado')?.value;
    if (cursoId && typeof MiniCalendar !== 'undefined') {
        new MiniCalendar('#miniCalendarContainer', {
            cursoId: cursoId
        });
    }
});

function abrirModalNuevaClase() {
    const modalEl = document.getElementById('modalClase');
    if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }
}
