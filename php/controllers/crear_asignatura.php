<?php
require_once '../conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['nombre_asignatura'], $data['clase_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    $conexion->beginTransaction(); // Usamos transacción para asegurar que se guardan ambas cosas

    // 1. Crear asignatura
    $color = $data['color_asignatura'] ?? '#4facfe';
    $icono = $data['icono_asignatura'] ?? 'fa-book';
    $stmt = $conexion->prepare("INSERT INTO asignaturas (nombre_asignatura, clase_id, color_asignatura, icono_asignatura) VALUES (?, ?, ?, ?)");
    $stmt->execute([trim($data['nombre_asignatura']), $data['clase_id'], $color, $icono]);
    $asig_id = $conexion->lastInsertId();

    // 2. Crear el periodo "Final" automáticamente
    $stmt_per = $conexion->prepare("INSERT INTO periodos_evaluacion (nombre_periodo, asignatura_id) VALUES ('Final', ?)");
    $stmt_per->execute([$asig_id]);

    $conexion->commit();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $conexion->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>