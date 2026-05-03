
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

    // Lógica para el selector de colores de Ajustes del Curso
    const ajusteDots = document.querySelectorAll('.color-dot-ajuste');
    const ajusteInput = document.getElementById('ajusteColor');
    
    if (ajusteInput && ajusteDots.length > 0) {
        // Inicializar el dot activo
        ajusteDots.forEach(d => {
            if (d.getAttribute('data-color') === ajusteInput.value) {
                d.style.boxShadow = '0 0 0 3px rgba(59,130,246,0.5)';
            }
        });

        ajusteDots.forEach(dot => {
            dot.addEventListener('click', function() {
                ajusteDots.forEach(d => d.style.boxShadow = 'none');
                this.style.boxShadow = '0 0 0 3px rgba(59,130,246,0.5)';
                ajusteInput.value = this.getAttribute('data-color');
            });
        });

        ajusteInput.addEventListener('input', function() {
            ajusteDots.forEach(d => d.style.boxShadow = 'none');
        });
    }

    // Lógica para enviar el formulario de Ajustes del Curso
    const formAjustes = document.getElementById('formAjustesCurso');
    if (formAjustes) {
        formAjustes.onsubmit = async function(e) {
            e.preventDefault();
            const payload = {
                id: document.getElementById('ajusteCursoId').value,
                nombre_centro: document.getElementById('ajusteNombreCentro').value,
                poblacion: document.getElementById('ajustePoblacion').value,
                provincia: document.getElementById('ajusteProvincia').value,
                anio: document.getElementById('ajusteAnio').value,
                color: document.getElementById('ajusteColor').value
            };

            try {
                const response = await fetch('guardar_curso.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error al guardar ajustes: ' + (data.error || 'Desconocido'));
                }
            } catch (error) {
                console.error("Error en la solicitud:", error);
                alert("Error de red. Inténtalo de nuevo.");
            }
        };
    }
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
    
    const claseId = document.getElementById('inputIdClase').value;
    if (claseId) {
        payload.id = claseId;
    }

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
        document.getElementById('formCrearClase').reset();
        document.getElementById('inputIdClase').value = '';
        document.getElementById('inputColorClase').value = '#3b82f6';
        document.getElementById('inputIconoClase').value = 'fa-users';
        document.getElementById('modalClaseLabel').innerText = 'Añadir Nueva Clase';
        
        document.querySelectorAll('.color-opcion').forEach((o, i) => o.classList.toggle('active', i === 0));
        document.querySelectorAll('.icono-opcion').forEach((o, i) => o.classList.toggle('active', i === 0));

        const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        bsModal.show();
    }
}
