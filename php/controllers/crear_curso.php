<?php
session_start(); // Iniciamos sesión para saber quién es el usuario
header('Content-Type: application/json');
require_once '../conexion.php';
require_once '../dao/cursoDAO.php';

// Verificamos si hay un usuario en la sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No hay sesión activa']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

try {
    $dao = new CursoDAO($conexion);
    // Añadimos el color al final
    $nuevo = new CursoVO(null, $data['centro'], $data['anio'], $data['poblacion'], $data['provincia'], $_SESSION['usuario_id'], $data['color']);
    
    if ($dao->insertar($nuevo)) {
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}