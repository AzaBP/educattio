
// --- LÓGICA DE SELECCIÓN DE COLORES E ICONOS ---
// Seleccionar Iconos
document.querySelectorAll('.icono-opcion').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.icono-opcion').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});

// Selección de color (incluyendo input[type="color"] visible)
document.querySelectorAll('.color-opcion').forEach(item => {
    if (item.tagName.toLowerCase() === 'input' && item.type === 'color') {
        // Input de color personalizado
        item.addEventListener('input', function() {
            // Al cambiar el color, marcar como activo y actualizar el valor
            document.querySelectorAll('.color-opcion').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            this.setAttribute('data-color', this.value);
        });
        item.addEventListener('click', function(e) {
            document.querySelectorAll('.color-opcion').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            this.setAttribute('data-color', this.value);
        });
    } else {
        // Presets
        item.addEventListener('click', function() {
            document.querySelectorAll('.color-opcion').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    }
});

let editClaseId = null;

function abrirModalEditarClase(id, nombre, materia, color, icono) {
    // Rellenar los campos del modal
    document.getElementById('inputIdClase').value = id;
    document.getElementById('inputNombreClase').value = nombre;
    document.getElementById('inputMateria').value = materia;
    document.getElementById('inputColorClase').value = color;
    document.getElementById('inputIconoClase').value = icono;

    // Seleccionar visualmente el color
    document.querySelectorAll('.color-opcion').forEach(el => {
        if (el.getAttribute('data-color') === color) {
            el.classList.add('active');
        } else {
            el.classList.remove('active');
        }
    });
    // Seleccionar visualmente el icono
    document.querySelectorAll('.icono-opcion').forEach(el => {
        if (el.getAttribute('data-icon') === icono) {
            el.classList.add('active');
        } else {
            el.classList.remove('active');
        }
    });

    // Cambiar título
    const tituloModal = document.getElementById('modalClaseLabel') || document.querySelector('#modalClase .modal-header h3');
    if (tituloModal) tituloModal.innerText = 'Modificar Clase';
    // Mostrar el modal usando Bootstrap Modal JS
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var miModal = new bootstrap.Modal(document.getElementById('modalClase'));
        miModal.show();
    } else {
        openModalClase();
    }
}

// --- MODAL DE NUEVA CLASE ---
function abrirModalNuevaClase() {
    // Vaciar los campos del nuevo modal
    const idClase = document.getElementById('id_clase');
    const nombreClase = document.getElementById('nombre_clase');
    const materiaClase = document.getElementById('materia_clase');
    const colorClase = document.getElementById('color_clase');
    if (idClase) idClase.value = '';
    if (nombreClase) nombreClase.value = '';
    if (materiaClase) materiaClase.value = '';
    if (colorClase) colorClase.value = '#3498db';

    // Cambiar el título si existe
    const tituloModal = document.getElementById('modalClaseLabel') || document.querySelector('#modalClase .modal-header h3');
    if (tituloModal) tituloModal.innerText = 'Añadir Nueva Clase';

    // Mostrar el modal usando Bootstrap Modal JS
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var miModal = new bootstrap.Modal(document.getElementById('modalClase'));
        miModal.show();
    } else {
        // Fallback: mostrar con la clase .active
        openModalClase();
    }
}

// Enlazar botón Editar con abrirModalEditarClase
async function prepararEdicion(id) {
    try {
        const response = await fetch(`controllers/get_detalles_clase.php?id=${id}`);
        const data = await response.json();
        if (data.status === 'success') {
            abrirModalEditarClase(
                data.clase.id,
                data.clase.nombre_clase,
                data.clase.materia_principal,
                data.clase.color_clase,
                data.clase.icono_clase
            );
        } else {
            alert('No se pudo cargar la información de la clase.');
        }
    } catch (error) {
        alert('Error al cargar la clase.');
    }
}
// --- CONTRASTE YIQ PARA TEXTO SOBRE COLORES ---
function getContrasteYIQ(hexcolor) {
    hexcolor = hexcolor.replace('#', '');
    const r = parseInt(hexcolor.substr(0,2),16);
    const g = parseInt(hexcolor.substr(2,2),16);
    const b = parseInt(hexcolor.substr(4,2),16);
    const yiq = ((r*299)+(g*587)+(b*114))/1000;
    return (yiq >= 200) ? '#333333' : '#ffffff';
}

// --- SELECCIÓN DE COLOR EN MODAL DE CLASES ---
function selectColorClase(color, element) {
    document.getElementById('inputColorClase').value = color;
    document.querySelectorAll('.color-dot').forEach(dot => dot.classList.remove('selected'));
    element.classList.add('selected');
}
function deselectPresetsClase() {
    document.querySelectorAll('.color-dot').forEach(dot => dot.classList.remove('selected'));
}

// --- SELECCIÓN DE ICONO EN MODAL DE CLASES ---
function selectIconClase(icon, element) {
    document.getElementById('inputIconoClase').value = icon;
    document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
}

/* ==========================================
   GESTIÓN DE MODALES
   ========================================== */
function openModalClase() {
    document.getElementById('modalClase').classList.add('active');
}

function closeModalClase() {
    document.getElementById('modalClase').classList.remove('active');
}


// --- Nueva lógica para evitar cierre accidental del modal al seleccionar texto ---
let clickEmpezoDentro = false;
// Si el usuario hace clic (mousedown) dentro del contenido, lo registramos
document.querySelectorAll('.modal-content').forEach(contenido => {
    contenido.addEventListener('mousedown', function() {
        clickEmpezoDentro = true;
    });
});
// Al soltar el clic (mouseup) en cualquier parte de la ventana
window.addEventListener('mouseup', function(event) {
    let modales = document.querySelectorAll('.modal');
    modales.forEach(modal => {
        // Solo cerramos si soltó el clic en el fondo oscuro Y no empezó dentro del contenido
        if (event.target === modal && !clickEmpezoDentro) {
            // Usar la función de cierre si existe
            if (typeof closeModalClase === 'function' && modal.id === 'modalClase') {
                closeModalClase();
            } else {
                modal.style.display = "none";
            }
        }
    });
    // Reseteamos la variable para el próximo clic
    clickEmpezoDentro = false;
});

/* ==========================================
   CARGA DINÁMICA DE DATOS
   ========================================== */
document.addEventListener('DOMContentLoaded', () => {
    // Evitar bucle de login: solo verificar sesión si no estamos en la página de login
    if (!window.location.pathname.endsWith('inicio_sesion.html')) {
        verificarSesion();
    }
    const urlParams = new URLSearchParams(window.location.search);
    const cursoId = urlParams.get('id');

    if (cursoId) {
        cargarDatosPantalla(cursoId);
        // Escuchador para el formulario de crear clase
        const formClase = document.getElementById('formCrearClase');
        if(formClase) {
            formClase.addEventListener('submit', guardarNuevaClase);
        }
    } else {
        console.error("No se encontró el ID del curso en la URL");
    }
});

// Al inicio de cargar la página
async function verificarSesion() {
    try {
        const res = await fetch('controllers/get_cursos.php');
        const data = await res.json();
        console.log("Estado de la sesión:", data); // Mira esto en la consola (F12)
        if (
            data.status === 'error' && 
            (data.message === 'No autorizado' || data.message === 'No hay sesión activa')
        ) {
            // Solo redirigir si realmente no estamos en login
            if (!window.location.pathname.includes('inicio_sesion.html')) {
                window.location.href = 'inicio_sesion.html';
            }
        }
    } catch (e) {
        console.error("Error verificando sesión:", e);
    }
}

async function cargarDatosPantalla(id) {
    try {
        const response = await fetch(`controllers/get_detalles_curso.php?id=${id}`);
        const data = await response.json();

        if (data.status === 'success') {
            document.querySelector('.header-content h1').textContent = data.curso.nombre_centro;
            document.querySelector('.badge.year').textContent = data.curso.anio_academico;
            document.querySelector('.badge.location').innerHTML = 
                `<i class="fas fa-map-marker-alt"></i> ${data.curso.poblacion}, ${data.curso.provincia}`;

            renderizarTarjetasClases(data.clases);
        }
    } catch (error) {
        console.error("Error al obtener datos:", error);
    }
}


// Función para decidir si el texto debe ser blanco o negro según el fondo
function getContrasteYIQ(hexcolor){
    hexcolor = hexcolor.replace("#", "");
    const r = parseInt(hexcolor.substr(0,2),16);
    const g = parseInt(hexcolor.substr(2,2),16);
    const b = parseInt(hexcolor.substr(4,2),16);
    const yiq = ((r*299)+(g*587)+(b*114))/1000;
    // Usar texto oscuro si el fondo es claro
    return (yiq >= 128) ? '#333333' : '#ffffff';
}

function renderizarTarjetasClases(clases) {
    const contenedor = document.querySelector('.groups-grid');
    let html = '';
    
    clases.forEach(clase => {
        const id = clase.id;
        const nombre = clase.nombre_clase;
        const materia = clase.materia_principal;
        const color = clase.color_clase || '#3498db'; // Color por defecto si falla
        const icono = clase.icono_clase || 'fa-graduation-cap'; // Icono por defecto

        // Calcular contraste para que el icono y los tres puntos se vean siempre bien
        const r = parseInt(color.substr(1, 2), 16);
        const g = parseInt(color.substr(3, 2), 16);
        const b = parseInt(color.substr(5, 2), 16);
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        const contrastColor = (yiq >= 128) ? '#333333' : '#ffffff';

        html += `
            <div class="group-card-wrapper" style="position: relative; z-index: 2;">
                <div class="group-card">
                    <a href="cuaderno.php?id=${id}" style="text-decoration: none; color: inherit;">
                        <div class="group-header" style="background-color: ${color}; color: ${contrastColor};">
                            <span class="group-icon"><i class="fas ${icono}"></i></span>
                        </div>
                        <div class="group-body">
                            <h3>${nombre}</h3>
                            <p class="subtitle">${materia}</p>
                            <div class="group-stats">
                                <span><i class="fas fa-user-friends"></i> Gestionar alumnos</span>
                            </div>
                        </div>
                    </a>
                    
                    <div class="card-options">
                        <button class="menu-dots" style="background: transparent; color: ${contrastColor};" onclick="event.stopPropagation(); toggleMenu(event, ${id})">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div id="dropdown-${id}" class="menu-opciones-aislado">
                            <a href="#" class="item-opcion" onclick="event.stopPropagation(); prepararEdicion(${id})">
                                <i class="fas fa-pen"></i> Modificar
                            </a>
                            <a href="#" class="item-opcion delete-option" onclick="event.stopPropagation(); eliminarClase(${id})">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    // Usar el HTML original del botón de añadir clase
    const htmlBotonAdd = `
        <div class="add-card-dashed" onclick="abrirModalNuevaClase()">
            <div class="add-icon">
                <i class="fas fa-plus"></i>
            </div>
            <h3>Añadir Nueva Clase</h3>
        </div>
    `;
    contenedor.innerHTML = html + htmlBotonAdd;
}

// --- Control de menú de opciones ---
function toggleMenu(event, id) {
    event.preventDefault();
    event.stopPropagation(); // Evita que al hacer clic se abra la clase
    // Cerrar otros menús abiertos
    document.querySelectorAll('.menu-opciones-aislado').forEach(m => {
        if(m.id !== `dropdown-${id}`) m.classList.remove('show');
    });
    // Abrir el actual
    const menu = document.getElementById(`dropdown-${id}`);
    menu.classList.toggle('show');
}

// Cerrar menús al hacer clic en cualquier otra parte
document.addEventListener('click', () => {
    document.querySelectorAll('.menu-opciones-aislado').forEach(m => m.classList.remove('show'));
});

/* ==========================================
   ACCIONES (CREAR Y ELIMINAR)
   ========================================== */


// --- FUNCIÓN PARA GUARDAR / EDITAR CLASE ---
async function guardarNuevaClase(e) {
    e.preventDefault();
    const cursoId = document.getElementById('cursoIdAsociado').value;
    // Buscar qué color e icono tienen la clase 'active' en este momento
    const colorActivo = document.querySelector('.color-opcion.active');
    const iconoActivo = document.querySelector('.icono-opcion.active');

    if (!colorActivo || !iconoActivo) {
        alert("Por favor, selecciona un color y un icono.");
        return;
    }

    const datos = {
        nombre_clase: document.getElementById('inputNombreClase').value,
        materia_principal: document.getElementById('inputMateria').value,
        color_clase: colorActivo.getAttribute('data-color'),
        icono_clase: iconoActivo.getAttribute('data-icon'),
        curso_id: cursoId
    };
    let url = 'controllers/crear_clase.php';
    if (typeof editClaseId !== 'undefined' && editClaseId) {
        url = 'controllers/editar_clase.php';
        datos.id = editClaseId;
    }
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        const resultado = await response.json();
        if (resultado.status === 'success') {
            closeModalClase();
            document.getElementById('formCrearClase').reset();
            cargarDatosPantalla(cursoId);
            // Resetear modo edición y título
            if (typeof editClaseId !== 'undefined') editClaseId = null;
            document.querySelector('#modalClase .modal-header h3').textContent = 'Crear Clase';
        } else {
            alert("Error al guardar: " + resultado.message);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

async function eliminarClase(idClase) {
    if (!confirm("¿Estás seguro de que quieres eliminar esta clase? Se borrarán todos sus alumnos y notas.")) {
        return;
    }

    try {
        const response = await fetch('controllers/eliminar_clase.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idClase })
        });
        const resultado = await response.json();

        if (resultado.status === 'success') {
            const urlParams = new URLSearchParams(window.location.search);
            cargarDatosPantalla(urlParams.get('id')); // Recargar la lista
        } else {
            alert("Error al eliminar: " + resultado.message);
        }
    } catch (error) {
        console.error("Error:", error);
    }
}