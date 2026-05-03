<?php
require_once '../conexion.php';
header('Content-Type: application/json');

$clase_id = $_GET['id'] ?? null;

if (!$clase_id) {
    echo json_encode(['status' => 'error', 'message' => 'Falta ID de clase']);
    exit;
}

try {
    // 1. Obtener Asignaturas y sus Periodos
    $sql_asig = "SELECT id, nombre_asignatura, color_asignatura, icono_asignatura FROM asignaturas WHERE clase_id = :id";
    $stmt = $conexion->prepare($sql_asig);
    $stmt->execute([':id' => $clase_id]);
    $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($asignaturas as &$asig) {
        $sql_per = "SELECT id, nombre_periodo FROM periodos_evaluacion WHERE asignatura_id = :asig_id";
        $stmt_per = $conexion->prepare($sql_per);
        $stmt_per->execute([':asig_id' => $asig['id']]);
        $asig['periodos'] = $stmt_per->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Obtener Alumnos (con foto, observaciones y datos personales)
    $sql_alumnos = "SELECT id, nombre_alumno, foto, observaciones, datos_personales FROM alumnos WHERE clase_id = :id ORDER BY nombre_alumno ASC";
    $stmt_al = $conexion->prepare($sql_alumnos);
    $stmt_al->execute([':id' => $clase_id]);
    $alumnos = $stmt_al->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'asignaturas' => $asignaturas,
        'alumnos' => $alumnos
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}