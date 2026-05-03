// Ya no se actualiza el color del header del modal
function actualizarVistaPreviaColor(color) {
    // No hacer nada, se elimina el efecto visual en el header
}
// Función para decidir si el texto debe ser blanco o negro según el fondo
function getContrasteYIQ(hexcolor){
    hexcolor = hexcolor.replace("#", "");
    const r = parseInt(hexcolor.substr(0,2),16);
    const g = parseInt(hexcolor.substr(2,2),16);
    const b = parseInt(hexcolor.substr(4,2),16);
    const yiq = ((r*299)+(g*587)+(b*114))/1000;
    // Solo usar texto oscuro si el fondo es MUY claro (luminosidad > 200)
    return (yiq >= 200) ? '#333333' : '#ffffff';
}
// Selector de color visual
function selectPresetColor(color, element) {
    document.getElementById('inputColor').value = color;
    document.querySelectorAll('.color-dot').forEach(dot => dot.classList.remove('selected'));
    element.classList.add('selected');
    actualizarVistaPreviaColor(color);
}

function deselectPresets() {
    document.querySelectorAll('.color-dot').forEach(dot => dot.classList.remove('selected'));
    // Cuando el usuario elige un color personalizado, actualizamos la vista previa
    const color = document.getElementById('inputColor').value;
    actualizarVistaPreviaColor(color);
}
// Abre el modal y rellena los campos para editar un curso existente
async function abrirModalEditar(id) {
    try {
        const response = await fetch(`controllers/get_detalles_curso.php?id=${id}`);
        const text = await response.text();
        try {
            const res = JSON.parse(text);
            if (res.status === 'success') {
                const c = res.curso;
                document.getElementById('editCursoId').value = c.id;
                document.getElementById('inputNombreCentro').value = c.nombre_centro;
                document.getElementById('inputAnio').value = c.anio_academico;
                document.getElementById('inputPoblacion').value = c.poblacion;
                document.getElementById('inputProvincia').value = c.provincia;
                document.getElementById('inputColor').value = c.color || '#ff7a59';

                // Marcar visualmente el círculo si coincide
                document.querySelectorAll('.color-dot').forEach(dot => {
                    if(dot.dataset.color === c.color) dot.classList.add('selected');
                    else dot.classList.remove('selected');
                });

                // Actualizar la vista previa del color en el modal
                actualizarVistaPreviaColor(c.color || '#ff7a59');

                document.querySelector('.modal-header h3').innerText = "Modificar Curso";
                openModal();
            }
        } catch (e) {
            console.error("Respuesta no válida del servidor:", text);
        }
    } catch (error) {
        console.error("Error de red:", error);
    }
}

// Función para abrir el modal
function openModal() {
    // Asegúrate de que en tu HTML el modal tenga id="modalCurso"
    const modal = document.getElementById('modalCurso');
    if (modal) {
        modal.classList.add('active');
    } else {
        console.error("No se encontró el elemento con id='modalCurso'");
    }
}

// Función para cerrar el modal
function closeModal() {
    const modal = document.getElementById('modalCurso');
    if (modal) {
        modal.classList.remove('active');
    }
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
            if (typeof closeModal === 'function' && modal.id === 'modalCurso') {
                closeModal();
            } else {
                modal.style.display = "none";
            }
        }
    });
    // Reseteamos la variable para el próximo clic
    clickEmpezoDentro = false;
});

document.addEventListener('DOMContentLoaded', () => {
    // Evitar bucle de login: solo verificar sesión si no estamos en la página de login
    if (!window.location.pathname.endsWith('login.php')) {
        verificarSesion();
    }
    cargarCursos();
    
    const form = document.getElementById('formCrearCurso');
    if(form) form.addEventListener('submit', guardarNuevoCurso);
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
            if (!window.location.pathname.includes('login.php')) {
                window.location.href = 'login.php';
            }
        }
    } catch (e) {
        console.error("Error verificando sesión:", e);
    }
}

async function cargarCursos() {
    try {
        const response = await fetch('controllers/get_cursos.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            renderizarCursos(result.data);
        }
    } catch (error) {
        console.error("Error cargando cursos:", error);
    }
}

function renderizarCursos(cursos) {
    // 1. Buscamos tu contenedor original
    const contenedor = document.querySelector('.classes-grid');
    
    // 2. Botón de añadir que llama a openModalNuevo
    const htmlBotonAdd = `
        <div class="add-card-dashed" onclick="openModalNuevo()">
            <div class="add-icon">
                <i class="fas fa-plus"></i>
            </div>
            <h3>Añadir Nuevo Curso</h3>
        </div>
    `;
    
    // 3. Generamos las tarjetas respetando tu maquetación
    let htmlCursos = '';
    cursos.forEach(curso => {
        const bgColor = curso.color || '#4facfe';
        htmlCursos += `
            <div class="premium-card-wrapper" style="position: relative;">
                <div class="card-options-container">
                    <button class="menu-dots-btn" onclick="toggleMenuCurso(event, ${curso.id})">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div id="menu-curso-${curso.id}" class="dropdown-options-menu">
                        <a href="javascript:void(0)" onclick="event.stopPropagation(); abrirModalEditar(${curso.id})"><i class="fas fa-edit"></i> Modificar</a>
                        <a href="javascript:void(0)" class="delete-option" onclick="event.stopPropagation(); eliminarCurso(${curso.id})"><i class="fas fa-trash"></i> Eliminar</a>
                    </div>
                </div>
                <a href="detalles_curso.php?id=${curso.id}" class="premium-card" style="--accent-color: ${bgColor};">
                    <div class="card-banner">
                        <div class="card-icon"><i class="fas fa-university"></i></div>
                        <div class="card-badge">${curso.anio_academico}</div>
                    </div>
                    <div class="card-content">
                        <h3>${curso.nombre_centro}</h3>
                        <p>${curso.poblacion}, ${curso.provincia}</p>
                        <div class="card-footer">
                            <span>Ver curso</span>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </a>
            </div>
        `;
    });

    contenedor.innerHTML = htmlCursos + htmlBotonAdd;
}

async function guardarNuevoCurso(e) {
    e.preventDefault();
    console.log("Intentando guardar curso...");

    const idEdicion = document.getElementById('editCursoId').value;
    const url = 'guardar_curso.php'; // Usamos el controlador unificado

    const datos = {
        nombre_centro: document.getElementById('inputNombreCentro').value,
        anio: document.getElementById('inputAnio').value,
        poblacion: document.getElementById('inputPoblacion').value,
        provincia: document.getElementById('inputProvincia').value,
        color: document.getElementById('inputColor').value
    };
    if (idEdicion) datos.id = idEdicion;

    console.log("Datos capturados:", datos);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            console.log("¡Éxito!"); 
            closeModal();
            document.getElementById('formCrearCurso').reset();
            document.getElementById('editCursoId').value = '';
            document.querySelector('.modal-header h3').innerText = "Crear nuevo curso";
            cargarCursos();
        } else {
            alert("Error: " + (resultado.error || 'No se pudo guardar el curso'));
        }
    } catch (error) {
        console.error("Error en la petición FETCH:", error);
        alert("Error de red al intentar guardar el curso.");
    }
}

function toggleMenuCurso(event, id) {
    event.stopPropagation();
    // Cierra todos los menús desplegables activos
    document.querySelectorAll('.menu-opciones-aislado.show, .dropdown-options-menu.show').forEach(menu => {
        if (menu.id !== `menu-curso-${id}`) {
            menu.classList.remove('show');
        }
    });
    // Abre/cierra el menú seleccionado
    const menu = document.getElementById(`menu-curso-${id}`);
    if (menu) {
        menu.classList.toggle('show');
    }
}

// Cerrar menús al hacer clic fuera (siempre solo uno abierto)
document.addEventListener('click', function(event) {
    document.querySelectorAll('.menu-opciones-aislado.show, .dropdown-options-menu.show').forEach(menu => {
        menu.classList.remove('show');
    });
});

// Asegurar que solo un menú puede estar abierto a la vez, incluso tras renderizarCursos
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', function(event) {
        document.querySelectorAll('.menu-opciones-aislado.show, .dropdown-options-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }, true);
});

async function eliminarCurso(id) {
    if(!confirm("¿Borrar este curso? Se borrarán todas sus clases y alumnos.")) return;
    
    await fetch('controllers/eliminar_curso.php', {
        method: 'POST',
        body: JSON.stringify({id: id})
    });
    cargarCursos();
}

function openModalNuevo() {
    const form = document.getElementById('formCrearCurso');
    if (form) form.reset();
    document.getElementById('editCursoId').value = '';
    document.getElementById('inputColor').value = '#ff7a59'; // Color por defecto
    document.querySelector('.modal-header h3').innerText = "Crear nuevo curso";
    actualizarVistaPreviaColor('#ff7a59');
    openModal();
}