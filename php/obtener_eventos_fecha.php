<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['usuario_id'];
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

try {
    $stmt = $conexion->prepare("SELECT e.id, e.titulo, e.descripcion, e.fecha, e.tipo_evento,
                                       c.nombre_clase, c.materia_principal,
                                       cu.nombre_centro, cu.anio_academico, cu.poblacion, cu.provincia
                                FROM eventos e
                                LEFT JOIN clases c ON e.clase_id = c.id
                                LEFT JOIN cursos cu ON c.curso_id = cu.id
                                WHERE e.usuario_id = :uid AND DATE(e.fecha) = :fecha
                                ORDER BY e.fecha ASC");
    $stmt->execute([':uid' => $userId, ':fecha' => $fecha]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($eventos as &$evento) {
        $evento['subject'] = $evento['materia_principal'];
        $evento['location'] = trim(($evento['poblacion'] ?? '') . ' ' . ($evento['provincia'] ?? ''));
    }

    header('Content-Type: application/json');
    echo json_encode($eventos);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>