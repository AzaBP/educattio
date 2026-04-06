<?php
session_start();
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/cursoDAO.php';
require_once '../vo/cursoVO.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de curso no proporcionado']);
    exit;
}

try {
    $dao = new CursoDAO($conexion);
    
    // El orden del constructor debe coincidir con tu cursoVO.php:
    // ($id, $nombre_centro, $anio_academico, $poblacion, $provincia, $usuario_id, $color)
    $curso = new CursoVO(
        $data['id'],
        $data['centro'],
        $data['anio'],
        $data['poblacion'],
        $data['provincia'],
        $_SESSION['usuario_id'],
        $data['color']
    );

    if ($dao->actualizar($curso)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el curso']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}