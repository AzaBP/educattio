<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$asignaturaId = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$asignaturaId) {
    echo json_encode(['status' => 'error', 'message' => 'ID de asignatura no proporcionado']);
    exit;
}

try {
    $sql = "SELECT a.id, a.nombre_asignatura, a.clase_id, c.nombre_clase, c.curso_id, cu.nombre_centro, cu.anio_academico, cu.poblacion, cu.provincia
            FROM asignaturas a
            JOIN clases c ON a.clase_id = c.id
            JOIN cursos cu ON c.curso_id = cu.id
            WHERE a.id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $asignaturaId]);
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$asignatura) {
        echo json_encode(['status' => 'error', 'message' => 'Asignatura no encontrada']);
        exit;
    }

    $stmtAlumnos = $conexion->prepare('SELECT COUNT(*) FROM alumnos WHERE clase_id = :clase_id');
    $stmtAlumnos->execute([':clase_id' => $asignatura['clase_id']]);
    $cantidadAlumnos = intval($stmtAlumnos->fetchColumn());

    $stmtPeriodos = $conexion->prepare('SELECT id, nombre_periodo FROM periodos_evaluacion WHERE asignatura_id = :asignatura_id ORDER BY id ASC');
    $stmtPeriodos->execute([':asignatura_id' => $asignaturaId]);
    $periodos = $stmtPeriodos->fetchAll(PDO::FETCH_ASSOC);

    $stmtTemas = $conexion->prepare('SELECT id, titulo, descripcion FROM temas_asignatura WHERE asignatura_id = :asignatura_id ORDER BY id ASC');
    $stmtTemas->execute([':asignatura_id' => $asignaturaId]);
    $temas = $stmtTemas->fetchAll(PDO::FETCH_ASSOC);

    $stmtEventos = $conexion->prepare('SELECT e.id, e.titulo, e.descripcion, e.tipo_evento, e.fecha, c.nombre_clase
                                        FROM eventos e
                                        JOIN clases c ON e.clase_id = c.id
                                        WHERE e.clase_id = :clase_id
                                        ORDER BY e.fecha ASC
                                        LIMIT 5');
    $stmtEventos->execute([':clase_id' => $asignatura['clase_id']]);
    $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($eventos as &$evento) {
        $evento['fecha_formateada'] = date('d/m/Y H:i', strtotime($evento['fecha']));
    }

    echo json_encode([
        'status' => 'success',
        'asignatura' => $asignatura,
        'clase' => [
            'id' => $asignatura['clase_id'],
            'nombre_clase' => $asignatura['nombre_clase']
        ],
        'curso' => [
            'nombre_centro' => $asignatura['nombre_centro'],
            'anio_academico' => $asignatura['anio_academico'],
            'poblacion' => $asignatura['poblacion'],
            'provincia' => $asignatura['provincia']
        ],
        'cantidad_alumnos' => $cantidadAlumnos,
        'periodos' => $periodos,
        'temas' => $temas,
        'eventos' => $eventos
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
