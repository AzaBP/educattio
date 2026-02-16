// Funciones para el Modal de Crear Clase
function openModalClase() {
    document.getElementById('modalClase').classList.add('active');
}

function closeModalClase() {
    document.getElementById('modalClase').classList.remove('active');
}

// Cerrar al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('modalClase');
    if (event.target == modal) {
        closeModalClase();
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