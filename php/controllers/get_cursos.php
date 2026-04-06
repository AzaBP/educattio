<?php
session_start();
header('Content-Type: application/json');

// 1. IMPORTANTE: Verifica que las rutas sean correctas
require_once '../conexion.php';
require_once '../dao/cursoDAO.php';
require_once '../vo/cursoVO.php'; // Asegúrate de incluir el VO si no lo hace el DAO

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

try {
    $dao = new CursoDAO($conexion);
    $lista = $dao->listarPorUsuario($_SESSION['usuario_id']);
    
    // Convertimos los objetos VO a arrays para el JSON
    $datos = [];
    foreach ($lista as $c) {
        $datos[] = [
            'id' => $c->id,
            'nombre_centro' => $c->nombre_centro,
            'anio_academico' => $c->anio_academico,
            'poblacion' => $c->poblacion,
            'provincia' => $c->provincia,
            'color' => $c->color // Esto ahora funcionará gracias a tu actualización del DAO
        ];
    }
    
    echo json_encode(['status' => 'success', 'data' => $datos]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}