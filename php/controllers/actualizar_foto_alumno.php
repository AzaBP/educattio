<?php
require_once '../conexion.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['foto'])) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos']); exit;
}

try {
    $stmt = $conexion->prepare("UPDATE alumnos SET foto = ? WHERE id = ?");
    $stmt->execute([$data['foto'], $data['id']]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>