<?php
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/asignaturaDAO.php';

if (!isset($_GET['clase_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el ID de la clase']);
    exit;
}

try {
    $dao = new AsignaturaDAO($conexion);
    $asignaturas = $dao->listarPorClase($_GET['clase_id']);
    
    // Convertimos los objetos VO a un array asociativo para JSON
    $result = array_map(function($a) {
        return [
            'id' => $a->getId(),
            'nombre_asignatura' => $a->getNombreAsignatura(),
            'clase_id' => $a->getClaseId()
        ];
    }, $asignaturas);

    echo json_encode(['status' => 'success', 'data' => $result]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>