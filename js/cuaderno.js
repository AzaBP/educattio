// cuaderno.js
document.querySelectorAll('.grade-input').forEach(input => {
    input.addEventListener('change', function() {
        const alumnoId = this.dataset.alumno;
        calcularMedia(alumnoId);
        guardarNotaBD(alumnoId, this.dataset.item, this.value);
    });
});

function calcularMedia(alumnoId) {
    const notasAlumno = document.querySelectorAll(`.grade-input[data-alumno="${alumnoId}"]`);
    let sumaPonderada = 0;
    let sumaPesos = 0;

    notasAlumno.forEach(input => {
        const nota = parseFloat(input.value) || 0;
        const peso = parseFloat(input.dataset.peso);
        
        sumaPonderada += (nota * peso);
        sumaPesos += peso;
    });

    const media = sumaPesos > 0 ? (sumaPonderada / sumaPesos) : 0;
    const celdaMedia = document.getElementById(`media-${alumnoId}`);
    celdaMedia.textContent = media.toFixed(2);
    
    // Feedback visual según la nota
    celdaMedia.style.color = media >= 5 ? '#27ae60' : '#e74c3c';
}

// Actualización de la función guardarNotaBD en tu cuaderno.js
function guardarNotaBD(alumnoId, itemId, valor, asignaturaId = 1) {
    // 1. Seleccionamos el input que el profesor acaba de modificar
    const celdaNota = document.querySelector(`.grade-input[data-alumno="${alumnoId}"][data-item="${itemId}"]`);
    
    // Cambiamos el color temporalmente a amarillo/naranja para indicar "Guardando..."
    celdaNota.style.backgroundColor = '#fff3cd';

    // 2. Preparamos el paquete de datos que vamos a enviar
    const datos = {
        alumno_id: alumnoId,
        item_id: itemId,
        nota: valor,
        asignatura_id: asignaturaId
    };

    // 3. Hacemos la llamada silenciosa a guardar_nota.php
    fetch('../php/guardar_nota.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datos) // Convertimos los datos a texto JSON
    })
    .then(response => response.json()) // Recibimos la respuesta de PHP
    .then(data => {
        if(data.status === 'success') {
            // ¡Éxito! Ponemos la casilla verde un segundito y luego la devolvemos a blanco
            celdaNota.style.backgroundColor = '#d4edda'; // Verde éxito
            setTimeout(() => { 
                celdaNota.style.backgroundColor = 'white'; 
            }, 800);
            console.log("Nota guardada: " + data.accion);
        } else {
            // Si el PHP devolvió un error (ej. faltaban datos)
            throw new Error(data.mensaje);
        }
    })
    .catch(error => {
        // Si hubo un error de conexión o del servidor, ponemos la casilla en rojo
        console.error('Error al guardar la nota:', error);
        celdaNota.style.backgroundColor = '#f8d7da'; // Rojo error
        alert('Hubo un problema al guardar la nota. Revisa tu conexión.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Al cargar la página, calculamos la media de todos para que no salga 0.00
    const alumnosIds = [...new Set(Array.from(document.querySelectorAll('.grade-input')).map(inp => inp.dataset.alumno))];
    alumnosIds.forEach(id => calcularMedia(id));

    // 2. Escuchar cambios en cualquier input de nota
    document.querySelectorAll('.grade-input').forEach(input => {
        
        // Cuando el profesor teclea y cambia de celda (evento change)
        input.addEventListener('change', function() {
            const alumnoId = this.dataset.alumno;
            const itemId = this.dataset.item;
            const nota = this.value;

            // Recalcular la nota final a la derecha
            calcularMedia(alumnoId);

            // Guardar en la base de datos (se conecta con guardar_nota.php que hicimos antes)
            guardarNotaBD(alumnoId, itemId, nota, this);
        });

        // Limitar la entrada para que no metan letras o números mayores a 10
        input.addEventListener('input', function() {
            if (this.value > 10) this.value = 10;
            if (this.value < 0) this.value = 0;
        });
    });
});

// Función matemática para calcular la nota final
function calcularMedia(alumnoId) {
    const notasAlumno = document.querySelectorAll(`.grade-input[data-alumno="${alumnoId}"]`);
    let sumaPonderada = 0;
    let sumaPesos = 0;

    notasAlumno.forEach(input => {
        const nota = parseFloat(input.value);
        const peso = parseFloat(input.dataset.peso);
        
        // Solo sumamos si hay un número escrito (para no penalizar columnas vacías aún no evaluadas)
        if (!isNaN(nota)) {
            sumaPonderada += (nota * peso);
            sumaPesos += peso;
        }
    });

    const celdaMedia = document.getElementById(`media-${alumnoId}`);
    
    if (sumaPesos > 0) {
        // Calculamos sobre 100 basándonos en los pesos rellenados
        const media = sumaPonderada / sumaPesos; 
        celdaMedia.textContent = media.toFixed(2);
        
        // Cambiamos el color según si aprueba o suspende
        if (media >= 5) {
            celdaMedia.style.color = '#27ae60'; // Verde
        } else {
            celdaMedia.style.color = 'var(--accent-color)'; // Rojo de tu diseño
        }
    } else {
        celdaMedia.textContent = "-";
        celdaMedia.style.color = '#999';
    }
}

// Envío AJAX al servidor
function guardarNotaBD(alumnoId, itemId, valor, inputElement) {
    // Feedback visual "Guardando"
    inputElement.style.backgroundColor = '#fff3cd'; 

    const datos = {
        alumno_id: alumnoId,
        item_id: itemId,
        nota: valor
    };

    fetch('../php/guardar_nota.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            // Guardado ok: iluminamos en verde un instante
            inputElement.style.backgroundColor = '#d4edda';
            setTimeout(() => { inputElement.style.backgroundColor = 'transparent'; }, 500);
        } else {
            throw new Error(data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Error: iluminamos en rojo
        inputElement.style.backgroundColor = '#f8d7da';
        setTimeout(() => { inputElement.style.backgroundColor = 'transparent'; }, 2000);
    });
}