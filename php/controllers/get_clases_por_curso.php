<?php
header('Content-Type: application/json');
require_once '../conexion.php';

$cursoId = isset($_GET['curso_id']) ? intval($_GET['curso_id']) : null;
if (!$cursoId) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el ID del curso']);
    exit;
}

try {
    $stmt = $conexion->prepare('SELECT id, nombre_clase FROM clases WHERE curso_id = :curso_id ORDER BY nombre_clase');
    $stmt->execute([':curso_id' => $cursoId]);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'clases' => $clases]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
