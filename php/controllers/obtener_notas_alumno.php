<?php
header('Content-Type: application/json');
require_once '../conexion.php';

if (!isset($_GET['alumno_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de alumno no proporcionado']);
    exit;
}

$alumno_id = $_GET['alumno_id'];

try {
    // 1. Obtener las asignaturas en las que está el alumno
    $stmt = $conexion->prepare("
        SELECT a.id, a.nombre_asignatura 
        FROM asignaturas a
        JOIN alumnos_asignaturas aa ON a.id = aa.asignatura_id
        WHERE aa.alumno_id = ?
    ");
    $stmt->execute([$alumno_id]);
    $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];

    foreach ($asignaturas as $asig) {
        // 2. Obtener las evaluaciones (notas) para cada asignatura
        $stmtNotas = $conexion->prepare("
            SELECT e.nota, i.titulo, p.nombre_periodo
            FROM evaluaciones e
            JOIN items_evaluacion i ON e.item_id = i.id
            LEFT JOIN periodos_evaluacion p ON i.periodo_id = p.id
            WHERE e.alumno_id = ? AND e.asignatura_id = ?
            ORDER BY p.id ASC, i.id ASC
        ");
        $stmtNotas->execute([$alumno_id, $asig['id']]);
        $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

        $resultado[] = [
            'asignatura' => $asig['nombre_asignatura'],
            'items' => $notas
        ];
    }

    echo json_encode(['status' => 'success', 'notas' => $resultado]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
