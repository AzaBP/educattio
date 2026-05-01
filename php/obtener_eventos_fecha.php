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
$curso_id = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

try {
    if ($curso_id > 0) {
        // Filtrar eventos de las clases que pertenecen a este curso
        $sql = "SELECT e.id, e.titulo, e.descripcion, e.fecha, e.tipo_evento,
                       c.nombre_clase, c.materia_principal,
                       cu.nombre_centro, cu.anio_academico, cu.poblacion, cu.provincia
                FROM eventos e
                JOIN clases c ON e.clase_id = c.id
                JOIN cursos cu ON c.curso_id = cu.id
                WHERE cu.id = :curso_id AND DATE(e.fecha) = :fecha
                ORDER BY e.fecha ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':curso_id' => $curso_id, ':fecha' => $fecha]);
    } else {
        // Eventos generales del usuario (sin clase o con clase_id NULL)
        $sql = "SELECT e.id, e.titulo, e.descripcion, e.fecha, e.tipo_evento,
                       NULL AS nombre_clase, NULL AS materia_principal,
                       NULL AS nombre_centro, NULL AS anio_academico, NULL AS poblacion, NULL AS provincia
                FROM eventos e
                WHERE e.usuario_id = :uid AND DATE(e.fecha) = :fecha AND e.clase_id IS NULL
                ORDER BY e.fecha ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':uid' => $userId, ':fecha' => $fecha]);
    }

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