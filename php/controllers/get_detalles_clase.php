<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/claseDAO.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID de clase no proporcionado']);
    exit;
}

try {
    $claseDAO = new ClaseDAO($conexion);
    $clase = $claseDAO->obtenerPorId($id);
    if ($clase) {
        echo json_encode(['status' => 'success', 'clase' => $clase]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Clase no encontrada']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
