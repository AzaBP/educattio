
/* ==========================================
   INTERACTIVIDAD DE DETALLES DEL CURSO
   ========================================== */

function actualizarReloj() {
    const ahora = new Date();
    const dName = document.getElementById('current-day-name');
    const dNumber = document.getElementById('current-day-number');
    const mYear = document.getElementById('current-month-year');
    const clock = document.getElementById('real-time-clock');

    if (dName) dName.innerText = ahora.toLocaleDateString('es-ES', { weekday: 'long' }).toUpperCase();
    if (dNumber) dNumber.innerText = ahora.getDate();
    if (mYear) mYear.innerText = ahora.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' }).toUpperCase();
    if (clock) clock.innerText = ahora.toLocaleTimeString('es-ES');
}

function initModalLogic() {
    document.querySelectorAll('.icono-opcion').forEach(item => {
        item.addEventListener('click', function () {
            document.querySelectorAll('.icono-opcion').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            const hidden = document.getElementById('inputIconoClase');
            if (hidden) hidden.value = this.getAttribute('data-icon');
        });
    });

    document.querySelectorAll('.color-opcion').forEach(item => {
        if (item.id === 'input-color-personalizado') {
            item.addEventListener('input', function () {
                document.querySelectorAll('.color-opcion').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                const hidden = document.getElementById('inputColorClase');
                if (hidden) hidden.value = this.value;
            });
        } else {
            item.addEventListener('click', function () {
                document.querySelectorAll('.color-opcion').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                const hidden = document.getElementById('inputColorClase');
                if (hidden) hidden.value = this.getAttribute('data-color');
            });
        }
    });
}

document.getElementById('formCrearClase').onsubmit = async function (e) {
    e.preventDefault();
    const cursoId = document.getElementById('cursoIdAsociado').value;
    const nombre = document.getElementById('inputNombreClase').value;
    const materia = document.getElementById('inputMateria').value;
    const color = document.getElementById('inputColorClase').value;
    const icono = document.getElementById('inputIconoClase').value;

    const payload = {
        curso_id: cursoId,
        nombre_clase: nombre,
        materia_principal: materia,
        color: color,
        icono: icono
    };

    try {
        const response = await fetch('guardar_clase.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            const modalEl = document.getElementById('modalClase');
            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.hide();
            if (window.EducattioUI) window.EducattioUI.success('Clase creada correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (e) {
        console.error(e);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initModalLogic();
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
