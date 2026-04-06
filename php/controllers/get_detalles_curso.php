<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/cursoDAO.php';
require_once '../dao/claseDAO.php';

// 1. Validar que recibimos el ID
$cursoId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$cursoId) {
    echo json_encode(['status' => 'error', 'message' => 'ID de curso no proporcionado']);
    exit;
}

try {
    $cursoDAO = new CursoDAO($conexion);
    $claseDAO = new ClaseDAO($conexion);

    // 2. Obtener datos del curso (Centro, Año, etc.)
    $curso = $cursoDAO->obtenerPorId($cursoId);
    
    if (!$curso) {
        echo json_encode(['status' => 'error', 'message' => 'Curso no encontrado']);
        exit;
    }

    // 3. Obtener listado de clases de ese curso
    $clases = $claseDAO->listarPorCurso($cursoId);

    // Convertir objetos ClaseVO a arrays asociativos
    $clases_array = array_map(function($clase) {
        return [
            'id' => $clase->id,
            'nombre_clase' => $clase->nombre_clase,
            'materia_principal' => $clase->materia_principal,
            'curso_id' => $clase->curso_id,
            'color_clase' => $clase->color_clase,
            'icono_clase' => $clase->icono_clase
        ];
    }, $clases);

    // 4. Responder con éxito
    echo json_encode([
        'status' => 'success',
        'curso' => $curso,
        'clases' => $clases_array
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>