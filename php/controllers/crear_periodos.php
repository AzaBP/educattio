<?php
require_once '../conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['nombres_periodos'], $data['asignatura_id']) || !is_array($data['nombres_periodos'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $conexion->prepare("INSERT INTO periodos_evaluacion (nombre_periodo, asignatura_id) VALUES (?, ?)");
    
    // Recorremos el array y guardamos todos los periodos que nos envíen (1, 2 o 3)
    foreach($data['nombres_periodos'] as $nombre) {
        $stmt->execute([trim($nombre), $data['asignatura_id']]);
    }
    
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>